<?php

namespace App\Services;

use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\GhiDanh;
use App\Models\ThongBao;
use App\Models\PhongHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AdminScheduleService
{
    public function queueEnrollments(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return GhiDanh::query()
            ->with(['user', 'subject.category'])
            ->whereNull('lop_hoc_id')
            ->whereIn('status', [GhiDanh::STATUS_PENDING, GhiDanh::STATUS_APPROVED])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhereHas('subject', function (Builder $subjectQuery) use ($search) {
                        $subjectQuery->where('name', 'like', '%' . $search . '%');
                    });
                });
            })
            ->orderByRaw("case when status = '" . GhiDanh::STATUS_APPROVED . "' then 0 else 1 end")
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
    }

    public function paginateSchedules(array $filters): LengthAwarePaginator
    {
        $teacherId = $filters['teacher_id'] ?? null;
        $studentId = $filters['student_id'] ?? null;
        $courseId = $filters['course_id'] ?? null;
        $date = $filters['date'] ?? null;

        return KhoaHoc::query()
            ->with(['subject.category', 'teacher', 'enrollments.user', 'classRooms.room', 'classRooms.teacher', 'classRooms.schedules'])
            ->withCount([
                'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', GhiDanh::courseAccessStatuses()),
            ])
            ->whereNotNull('teacher_id')
            ->whereIn('status', [
                KhoaHoc::STATUS_PENDING_OPEN,
                KhoaHoc::STATUS_SCHEDULED,
                KhoaHoc::STATUS_ACTIVE,
            ])
            ->when($teacherId, fn (Builder $query) => $query->where('teacher_id', $teacherId))
            ->when($courseId, fn (Builder $query) => $query->whereKey($courseId))
            ->when($studentId, function (Builder $query) use ($studentId) {
                $query->whereHas('enrollments', function (Builder $enrollmentQuery) use ($studentId) {
                    $enrollmentQuery->where('user_id', $studentId)
                        ->whereIn('status', GhiDanh::courseAccessStatuses());
                });
            })
            ->when($date, function (Builder $query) use ($date) {
                $query->whereNotNull('start_date')
                    ->whereDate('start_date', '<=', $date)
                    ->where(function (Builder $builder) use ($date) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $date);
                    });
            })
            ->orderByRaw("case when status = '" . KhoaHoc::STATUS_PENDING_OPEN . "' then 0 when status = '" . KhoaHoc::STATUS_SCHEDULED . "' then 1 when status = '" . KhoaHoc::STATUS_ACTIVE . "' then 2 else 3 end")
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();
    }

    public function getSchedulingContext(GhiDanh $enrollment): array
    {
        $enrollment->load(['user', 'subject.category', 'course.teacher', 'assignedTeacher.department']);

        $courses = KhoaHoc::query()
            ->with(['teacher', 'subject'])
            ->where('subject_id', $enrollment->subject_id)
            ->orderBy('title')
            ->get();

        $waitingCourses = KhoaHoc::query()
            ->with(['teacher', 'subject'])
            ->withCount([
                'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', GhiDanh::courseAccessStatuses()),
            ])
            ->where('subject_id', $enrollment->subject_id)
            ->where('status', KhoaHoc::STATUS_PENDING_OPEN)
            ->orderBy('title')
            ->get();

        $teachers = $this->teacherOptionsForSubject($enrollment->subject);

        $selectedTeacher = $enrollment->assignedTeacher
            ?? $enrollment->course?->teacher;

        if ($selectedTeacher && ! $teachers->contains('id', $selectedTeacher->id)) {
            $teachers = $teachers->prepend($selectedTeacher)->unique('id')->values();
        }

        return [
            'enrollment' => $enrollment,
            'courses' => $courses,
            'waitingCourses' => $waitingCourses,
            'teachers' => $teachers,
            'rooms' => $this->roomOptions(),
            'suggestedCourseTitle' => $this->suggestedCourseTitle($enrollment),
            'minimumStudentsToOpen' => KhoaHoc::minimumStudentsToOpen(),
        ];
    }

    public function getOpenCourseContext(KhoaHoc $course): array
    {
        $course->load([
            'subject.category',
            'teacher',
            'enrollments.user',
        ])->loadCount([
            'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', GhiDanh::courseAccessStatuses()),
        ]);

        $rooms = $this->availableRoomsForCourse($course);

        return [
            'course' => $course,
            'minimumStudentsToOpen' => KhoaHoc::minimumStudentsToOpen(),
            'studentsNeeded' => max(0, KhoaHoc::minimumStudentsToOpen() - (int) $course->scheduled_students_count),
            'rooms' => $rooms,
            'availableRoomsCount' => $rooms->count(),
        ];
    }

    public function getCourseDetailContext(KhoaHoc $course): array
    {
        $course->load([
            'subject.category',
            'teacher',
            'enrollments.user',
            'enrollments.classRoom',
            'classRooms.room',
            'classRooms.teacher',
            'classRooms.schedules.room',
        ])->loadCount([
            'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', GhiDanh::courseAccessStatuses()),
        ]);

        $classRoom = $course->currentClassRoom();
        $classRoom?->loadMissing(['room', 'teacher', 'schedules.room']);

        $activeEnrollments = $course->enrollments
            ->filter(fn (GhiDanh $enrollment) => in_array($enrollment->status, GhiDanh::courseAccessStatuses(), true))
            ->values();

        GhiDanh::syncDisplayStatusesByClass($activeEnrollments);

        $activeEnrollments = $activeEnrollments
            ->sortBy(function (GhiDanh $enrollment) {
                return match ($enrollment->displayStatus()) {
                    GhiDanh::STATUS_SCHEDULED => 0,
                    GhiDanh::STATUS_ACTIVE => 1,
                    GhiDanh::STATUS_APPROVED => 2,
                    GhiDanh::STATUS_PENDING => 3,
                    default => 9,
                };
            })
            ->values();

        $classSchedules = $classRoom?->schedules
            ? $classRoom->schedules->sortBy(function (LichHoc $schedule) {
                return array_search($schedule->day_of_week, array_keys(LichHoc::$dayOptions), true);
            })->values()
            : collect();

        return [
            'course' => $course,
            'classRoom' => $classRoom,
            'classSchedules' => $classSchedules,
            'activeEnrollments' => $activeEnrollments,
            'scheduledStudentsCount' => (int) $course->scheduled_students_count,
            'minimumStudentsToOpen' => KhoaHoc::minimumStudentsToOpen(),
        ];
    }

    public function syncCourseSchedule(KhoaHoc $course, ?int $roomId = null): ?LopHoc
    {
        $course->loadMissing(['subject', 'classRooms.room', 'classRooms.schedules']);

        $meetingDays = $course->meetingDayValues();

        if ($meetingDays === [] || ! $course->start_time || ! $course->end_time) {
            return null;
        }

        $startDate = $course->start_date?->format('Y-m-d');
        $endDate = $course->end_date?->format('Y-m-d');
        $currentClassRoom = $course->currentClassRoom();

        if (! $startDate && $currentClassRoom?->start_date) {
            $startDate = $currentClassRoom->start_date->format('Y-m-d');
        }

        if (! $endDate && $startDate) {
            $duration = max(1, (int) ($course->subject?->duration ?? $currentClassRoom?->duration ?? 1));
            $endDate = Carbon::parse($startDate)->addMonths($duration)->format('Y-m-d');
        }

        if (! $startDate || ! $endDate) {
            return null;
        }

        $roomId ??= $currentClassRoom?->room_id;

        if (! $roomId || ! $course->teacher_id) {
            return null;
        }

        return $this->syncClassRoomForCourse(
            $course,
            (int) $roomId,
            (int) $course->teacher_id,
            $meetingDays,
            (string) $course->start_time,
            (string) $course->end_time,
            $startDate
        );
    }

    public function scheduleEnrollment(GhiDanh $enrollment, array $data, NguoiDung $admin): string
    {
        return DB::transaction(function () use ($enrollment, $data, $admin): string {
            $enrollment = GhiDanh::query()
                ->lockForUpdate()
                ->findOrFail($enrollment->id);

            $enrollment->loadMissing(['subject', 'user']);
            $course = $this->resolveCourse($enrollment, $data, true);

            if ($this->shouldCreateNewClass($enrollment) || $course->isPendingOpen()) {
                return $this->savePendingOpenEnrollment($enrollment, $course, $data, $admin);
            }

            $teacherId = $data['teacher_id'] ?? $course->teacher_id;
            $meetingDays = $this->resolveMeetingDays($course, $data);
            $startDate = $data['start_date'] ?? optional($course->start_date)?->format('Y-m-d');
            $endDate = $data['end_date'] ?? optional($course->end_date)?->format('Y-m-d');
            $startTime = $data['start_time'] ?? $course->start_time;
            $endTime = $data['end_time'] ?? $course->end_time;
            $capacity = $data['capacity'] ?? $course->capacity ?? 20;
            $roomId = $data['room_id'] ?? null;

            foreach ([
                'teacher_id' => $teacherId,
                'room_id' => $roomId,
                'day_of_week' => $meetingDays,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ] as $field => $value) {
                if ($value === null || $value === [] || $value === '') {
                    throw ValidationException::withMessages([
                        $field => 'Vui lòng cung cấp đầy đủ thông tin lịch học chính thức.',
                    ]);
                }
            }

            $this->ensureTeacherIsValid((int) $teacherId);
            $this->ensureRoomIsValid((int) $roomId);
            $this->ensureTeacherAvailability($course->id, (int) $teacherId, $meetingDays, (string) $startDate, (string) $endDate, (string) $startTime, (string) $endTime);
            $this->ensureRoomAvailability(
                (int) $roomId,
                $meetingDays,
                (string) $startTime,
                (string) $endTime,
                (string) $startDate,
                (string) $endDate,
                $this->findExistingClassRoomForCourse($course->id)?->id
            );
            $this->ensureStudentAvailability($enrollment, $course->id, $meetingDays, (string) $startDate, (string) $endDate, (string) $startTime, (string) $endTime);
            $this->ensureCapacity($course, (int) $capacity, $enrollment);

            $course->fill([
                'teacher_id' => (int) $teacherId,
                'day_of_week' => $meetingDays[0] ?? null,
                'meeting_days' => $meetingDays,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => (string) $startTime,
                'end_time' => (string) $endTime,
                'capacity' => (int) $capacity,
                'status' => KhoaHoc::STATUS_SCHEDULED,
            ]);
            $course->schedule = $this->buildScheduleSummary($course);
            $course->save();

            $classRoom = $this->syncClassRoomForCourse(
                $course,
                (int) $roomId,
                (int) $teacherId,
                $meetingDays,
                (string) $startTime,
                (string) $endTime,
                (string) $startDate,
            );

            $enrollmentPayload = [
                'course_id' => $course->id,
                'subject_id' => $course->subject_id,
                'lop_hoc_id' => $classRoom->id,
                'assigned_teacher_id' => (int) $teacherId,
                'schedule' => $course->schedule,
                'status' => GhiDanh::STATUS_SCHEDULED,
                'note' => $data['note'] ?? $enrollment->note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ];

            $enrollment->update($enrollmentPayload);
            $course->enrollments()
                ->whereIn('status', GhiDanh::courseAccessStatuses())
                ->update([
                    'lop_hoc_id' => $classRoom->id,
                    'assigned_teacher_id' => (int) $teacherId,
                    'schedule' => $course->schedule,
                ]);

            $this->refreshClassStatusByCapacity($classRoom);

            return $course->wasRecentlyCreated
                ? 'Đã tạo lớp học mới và xếp lịch thành công cho học viên.'
                : 'Đã xếp lịch chính thức cho học viên.';
        });
    }

    public function openPendingCourse(KhoaHoc $course, array $data, NguoiDung $admin): string
    {
        return DB::transaction(function () use ($course, $data, $admin): string {
            $course = KhoaHoc::query()
                ->lockForUpdate()
                ->findOrFail($course->id);

            $course->loadMissing(['teacher', 'subject', 'enrollments.user'])->loadCount([
                'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', GhiDanh::courseAccessStatuses()),
            ]);

            if (! $course->isPendingOpen()) {
                throw ValidationException::withMessages([
                    'course' => 'Lớp này không ở trạng thái chờ mở.',
                ]);
            }

            if ((int) $course->scheduled_students_count < KhoaHoc::minimumStudentsToOpen()) {
                throw ValidationException::withMessages([
                    'course' => 'Lớp chưa đủ tối thiểu ' . KhoaHoc::minimumStudentsToOpen() . ' học viên để mở.',
                ]);
            }

            $meetingDays = $course->meetingDayValues();
            $startDate = (string) $data['start_date'];
            $endDate = (string) $data['end_date'];
            $startTime = (string) $course->start_time;
            $endTime = (string) $course->end_time;
            $teacherId = (int) $course->teacher_id;
            $roomId = (int) $data['room_id'];

            foreach ([
                'teacher_id' => $teacherId,
                'room_id' => $roomId,
                'day_of_week' => $meetingDays,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ] as $field => $value) {
                if ($value === null || $value === [] || $value === '') {
                    throw ValidationException::withMessages([
                        $field => 'Vui lòng hoàn thiện đầy đủ lịch học trước khi mở lớp.',
                    ]);
                }
            }

            $this->ensureTeacherIsValid($teacherId);
            $this->ensureRoomIsValid($roomId);
            $this->ensureTeacherAvailability($course->id, $teacherId, $meetingDays, $startDate, $endDate, $startTime, $endTime);
            $this->ensureRoomAvailability(
                $roomId,
                $meetingDays,
                $startTime,
                $endTime,
                $startDate,
                $endDate,
                $this->findExistingClassRoomForCourse($course->id)?->id
            );

            $enrollments = $course->enrollments()
                ->whereIn('status', GhiDanh::courseAccessStatuses())
                ->lockForUpdate()
                ->get();

            foreach ($enrollments as $enrollment) {
                $this->ensureStudentAvailability($enrollment, $course->id, $meetingDays, $startDate, $endDate, $startTime, $endTime);
            }

            $course->fill([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => KhoaHoc::STATUS_SCHEDULED,
            ]);
            $course->schedule = $this->buildScheduleSummary($course);
            $course->save();

            $classRoom = $this->syncClassRoomForCourse(
                $course,
                $roomId,
                $teacherId,
                $meetingDays,
                $startTime,
                $endTime,
                $startDate,
            );

            $course->enrollments()
                ->whereIn('status', GhiDanh::courseAccessStatuses())
                ->update([
                    'lop_hoc_id' => $classRoom->id,
                    'assigned_teacher_id' => $teacherId,
                    'schedule' => $course->schedule,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                ]);

            $this->refreshClassStatusByCapacity($classRoom);
            $this->notifyStudentsCourseOpened($course);

            return 'Đã chốt ngày khai giảng và mở lớp thành công.';
        });
    }

    public function teacherOptions(): Collection
    {
        return NguoiDung::query()
            ->teachers()
            ->with('department')
            ->where('status', NguoiDung::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    public function teacherOptionsForSubject(?MonHoc $subject): Collection
    {
        $teachers = $this->teacherOptions();

        if (! $subject) {
            return $teachers;
        }

        $subject->loadMissing('category');

        $filteredTeachers = $teachers->filter(fn (NguoiDung $teacher) => $this->teacherMatchesSubject($teacher, $subject));

        return $filteredTeachers->values();
    }

    public function teacherMatchesSubject(NguoiDung $teacher, ?MonHoc $subject): bool
    {
        if (! $subject) {
            return true;
        }

        $subject->loadMissing('category');

        $subjectTexts = collect([
            $subject->name,
            $subject->category?->name,
            $subject->category?->slug,
            $subject->category?->program,
            $subject->category?->level,
        ])
            ->filter()
            ->map(fn (string $value) => $this->normalizeTeacherSubjectText($value))
            ->filter()
            ->values();

        $teacherTexts = collect([
            $teacher->specialtyLabel(),
            $teacher->department?->name,
            $teacher->department?->code,
        ])
            ->filter()
            ->map(fn (string $value) => $this->normalizeTeacherSubjectText($value))
            ->filter()
            ->values();

        if ($teacherTexts->isEmpty() || $subjectTexts->isEmpty()) {
            return true;
        }

        foreach ($teacherTexts as $teacherText) {
            foreach ($subjectTexts as $subjectText) {
                if ($teacherText === '' || $subjectText === '') {
                    continue;
                }

                if (str_contains($subjectText, $teacherText) || str_contains($teacherText, $subjectText)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function studentOptions(): Collection
    {
        return NguoiDung::query()
            ->students()
            ->whereHas('enrollments', fn (Builder $query) => $query->whereIn('status', GhiDanh::courseAccessStatuses()))
            ->orderBy('name')
            ->get();
    }

    public function courseOptions(): Collection
    {
        return KhoaHoc::query()
            ->orderBy('title')
            ->get();
    }

    public function classRoomOptions(): Collection
    {
        return LopHoc::query()
            ->with(['course.subject', 'room', 'teacher', 'schedules'])
            ->whereNotIn('status', [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED])
            ->orderByDesc('id')
            ->get();
    }

    public function roomOptions(): Collection
    {
        return PhongHoc::query()
            ->where('status', PhongHoc::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    public function availableRoomsForCourse(KhoaHoc $course): Collection
    {
        $meetingDays = $course->meetingDayValues();
        $startTime = (string) ($course->start_time ?? '');
        $endTime = (string) ($course->end_time ?? '');
        $startDate = $course->start_date?->format('Y-m-d');
        $endDate = $course->end_date?->format('Y-m-d');

        if ($meetingDays === [] || $startTime === '' || $endTime === '') {
            return $this->roomOptions();
        }

        return $this->roomOptions()
            ->filter(function (PhongHoc $room) use ($meetingDays, $startTime, $endTime, $startDate, $endDate): bool {
                return ! LopHoc::roomHasConflict(
                    $room->id,
                    $meetingDays,
                    $startTime,
                    $endTime,
                    $startDate,
                    $endDate
                );
            })
            ->values();
    }

    protected function normalizeTeacherSubjectText(string $value): string
    {
        return trim(Str::lower(Str::ascii($value)));
    }

    protected function savePendingOpenEnrollment(GhiDanh $enrollment, KhoaHoc $course, array $data, NguoiDung $admin): string
    {
        $teacherId = $data['teacher_id'] ?? $course->teacher_id;
        $meetingDays = $this->resolveMeetingDays($course, $data);
        $startTime = $data['start_time'] ?? $course->start_time;
        $endTime = $data['end_time'] ?? $course->end_time;
        $capacity = $data['capacity'] ?? $course->capacity ?? 20;

        foreach ([
            'teacher_id' => $teacherId,
            'day_of_week' => $meetingDays,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ] as $field => $value) {
            if ($value === null || $value === [] || $value === '') {
                throw ValidationException::withMessages([
                    $field => 'Vui lòng chọn giảng viên và khung giờ để lưu lớp chờ mở.',
                ]);
            }
        }

        $this->ensureTeacherIsValid((int) $teacherId);
        $this->ensureCapacity($course, (int) $capacity, $enrollment);
        $this->ensureStudentHasNoRequestedScheduleConflict(
            (int) $enrollment->user_id,
            $meetingDays,
            (string) $startTime,
            (string) $endTime,
            $enrollment->id
        );

        $course->fill([
            'teacher_id' => (int) $teacherId,
            'day_of_week' => $meetingDays[0] ?? null,
            'meeting_days' => $meetingDays,
            'start_date' => null,
            'end_date' => null,
            'start_time' => (string) $startTime,
            'end_time' => (string) $endTime,
            'capacity' => (int) $capacity,
            'status' => KhoaHoc::STATUS_PENDING_OPEN,
        ]);
        $course->schedule = $this->buildPendingOpenSummary($course);
        $course->save();

        $enrollment->update([
            'course_id' => $course->id,
            'subject_id' => $course->subject_id,
            'assigned_teacher_id' => (int) $teacherId,
            'schedule' => $course->schedule,
            'status' => GhiDanh::STATUS_SCHEDULED,
            'note' => $data['note'] ?? $enrollment->note,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $currentCount = $this->queuedStudentCount($course->fresh());
        $this->notifyStudentPendingOpen($enrollment->fresh(['user', 'subject', 'course']), $course->fresh(), $currentCount);

        if ($course->wasRecentlyCreated) {
            return 'Đã lưu lớp chờ mở. Hiện có ' . $currentCount . '/' . KhoaHoc::minimumStudentsToOpen() . ' học viên.';
        }

        return 'Đã ghép học viên vào lớp chờ mở. Hiện có ' . $currentCount . '/' . KhoaHoc::minimumStudentsToOpen() . ' học viên.';
    }

    protected function resolveMeetingDays(KhoaHoc $course, array $data): array
    {
        $selectedDays = $data['day_of_week'] ?? null;

        if (is_array($selectedDays) && $selectedDays !== []) {
            return array_values(array_unique($selectedDays));
        }

        if (is_string($selectedDays) && $selectedDays !== '') {
            return [$selectedDays];
        }

        return $course->meetingDayValues();
    }

    protected function resolveCourse(GhiDanh $enrollment, array $data, bool $lockForUpdate = false): KhoaHoc
    {
        if ($this->shouldCreateNewClass($enrollment)) {
            if (! empty($data['course_id'])) {
                $courseQuery = KhoaHoc::query();

                if ($lockForUpdate) {
                    $courseQuery->lockForUpdate();
                }

                $course = $courseQuery->findOrFail($data['course_id']);

                if ((int) $course->subject_id !== (int) $enrollment->subject_id) {
                    throw ValidationException::withMessages([
                        'course_id' => 'Lớp chờ mở phải thuộc đúng khóa học mà học viên đã đăng ký.',
                    ]);
                }

                if (! $course->isPendingOpen()) {
                    throw ValidationException::withMessages([
                        'course_id' => 'Yêu cầu lịch học riêng chỉ có thể ghép vào lớp đang chờ mở cùng môn.',
                    ]);
                }

                return $course;
            }

            $title = trim((string) ($data['new_course_title'] ?? ''));
            $title = $title !== '' ? $title : $this->suggestedCourseTitle($enrollment);

            return new KhoaHoc([
                'subject_id' => $enrollment->subject_id,
                'title' => $title,
                'description' => $data['new_course_description'] ?? null,
                'status' => KhoaHoc::STATUS_DRAFT,
            ]);
        }

        if (! empty($data['course_id'])) {
            $courseQuery = KhoaHoc::query();

            if ($lockForUpdate) {
                $courseQuery->lockForUpdate();
            }

            $course = $courseQuery->findOrFail($data['course_id']);
            if ((int) $course->subject_id !== (int) $enrollment->subject_id) {
                throw ValidationException::withMessages([
                    'course_id' => 'Lớp học được chọn không thuộc đúng khóa học mà học viên đã đăng ký.',
                ]);
            }

            return $course;
        }

        $title = trim((string) ($data['new_course_title'] ?? ''));
        $title = $title !== '' ? $title : $this->suggestedCourseTitle($enrollment);

        if ($title === '') {
            throw ValidationException::withMessages([
                'new_course_title' => 'Vui lòng nhập tên lớp học mới hoặc chọn lớp học có sẵn.',
            ]);
        }

        return new KhoaHoc([
            'subject_id' => $enrollment->subject_id,
            'title' => $title,
            'description' => $data['new_course_description'] ?? null,
            'status' => KhoaHoc::STATUS_DRAFT,
        ]);
    }

    protected function suggestedCourseTitle(GhiDanh $enrollment): string
    {
        $subjectName = trim((string) ($enrollment->subject?->name ?? 'Lớp học'));
        $existingTitles = KhoaHoc::query()
            ->where('subject_id', $enrollment->subject_id)
            ->pluck('title')
            ->filter(fn ($title) => is_string($title) && trim($title) !== '')
            ->values();

        $pattern = '/^' . preg_quote($subjectName, '/') . '\s*-\s*Khóa học\s+(\d+)$/u';
        $nextNumber = 1;

        foreach ($existingTitles as $existingTitle) {
            if (preg_match($pattern, (string) $existingTitle, $matches) === 1) {
                $nextNumber = max($nextNumber, ((int) $matches[1]) + 1);
            }
        }

        $candidate = $subjectName . ' - Khóa học ' . $nextNumber;

        while ($existingTitles->contains($candidate)) {
            $nextNumber++;
            $candidate = $subjectName . ' - Khóa học ' . $nextNumber;
        }

        return $candidate;
    }

    protected function shouldCreateNewClass(GhiDanh $enrollment): bool
    {
        return $enrollment->isCustomScheduleRequest()
            && in_array($enrollment->normalizedStatus(), [
                GhiDanh::STATUS_PENDING,
                GhiDanh::STATUS_APPROVED,
            ], true);
    }

    protected function ensureTeacherIsValid(int $teacherId): void
    {
        if (! NguoiDung::query()->teachers()->whereKey($teacherId)->exists()) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Giảng viên được chọn không hợp lệ.',
            ]);
        }
    }

    protected function ensureRoomIsValid(int $roomId): void
    {
        if (! PhongHoc::query()->where('status', PhongHoc::STATUS_ACTIVE)->whereKey($roomId)->exists()) {
            throw ValidationException::withMessages([
                'room_id' => 'Phòng học được chọn không hợp lệ hoặc đang tạm ngưng.',
            ]);
        }
    }

    protected function ensureTeacherAvailability(?int $excludeCourseId, int $teacherId, array $meetingDays, string $startDate, string $endDate, string $startTime, string $endTime): void
    {
        $conflict = KhoaHoc::query()
            ->where('teacher_id', $teacherId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereIn('status', KhoaHoc::schedulingStatuses())
            ->when($excludeCourseId, fn (Builder $query) => $query->whereKeyNot($excludeCourseId))
            ->whereDate('start_date', '<=', $endDate)
            ->where(function (Builder $query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            })
            ->get()
            ->first(fn (KhoaHoc $course) => $this->meetingDaysOverlap($meetingDays, $course->meetingDayValues()));

        if ($conflict) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Giảng viên đã có lịch trùng vào khung giờ này.',
            ]);
        }
    }

    protected function ensureStudentAvailability(GhiDanh $enrollment, ?int $excludeCourseId, array $meetingDays, string $startDate, string $endDate, string $startTime, string $endTime): void
    {
        $conflict = GhiDanh::query()
            ->where('user_id', $enrollment->user_id)
            ->whereKeyNot($enrollment->id)
            ->whereIn('status', [GhiDanh::STATUS_SCHEDULED, GhiDanh::STATUS_ACTIVE])
            ->with('course')
            ->whereHas('course', function (Builder $query) use ($excludeCourseId, $startDate, $endDate, $startTime, $endTime) {
                $query->when($excludeCourseId, fn (Builder $courseQuery) => $courseQuery->whereKeyNot($excludeCourseId))
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime)
                    ->whereIn('status', KhoaHoc::schedulingStatuses())
                    ->whereDate('start_date', '<=', $endDate)
                    ->where(function (Builder $builder) use ($startDate) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $startDate);
                    });
            })
            ->get()
            ->first(fn (GhiDanh $conflictEnrollment) => $conflictEnrollment->course !== null
                && $this->meetingDaysOverlap($meetingDays, $conflictEnrollment->course->meetingDayValues()));

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_date' => 'Học viên đã có lớp khác trùng lịch trong khung thời gian này.',
            ]);
        }
    }

    protected function ensureRoomAvailability(
        int $roomId,
        array $meetingDays,
        string $startTime,
        string $endTime,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $excludeClassRoomId = null
    ): void
    {
        if (LopHoc::roomHasConflict($roomId, $meetingDays, $startTime, $endTime, $startDate, $endDate, $excludeClassRoomId)) {
            throw ValidationException::withMessages([
                'room_id' => 'Phòng học đã có lớp khác trùng vào khung giờ này.',
            ]);
        }
    }

    protected function ensureCapacity(KhoaHoc $course, int $capacity, GhiDanh $enrollment): void
    {
        $currentCount = $course->exists
            ? $course->enrollments()
                ->whereIn('status', GhiDanh::courseAccessStatuses())
                ->when($enrollment->course_id === $course->id, fn (Builder $query) => $query->whereKeyNot($enrollment->id))
                ->count()
            : 0;

        if ($currentCount + 1 > $capacity) {
            throw ValidationException::withMessages([
                'capacity' => 'Sĩ số lớp học không đủ để xếp thêm học viên này.',
            ]);
        }
    }

    protected function buildScheduleSummary(KhoaHoc $course): string
    {
        $startDate = $course->start_date instanceof Carbon ? $course->start_date : Carbon::parse($course->start_date);
        $endDate = $course->end_date instanceof Carbon ? $course->end_date : Carbon::parse($course->end_date);

        return $course->meetingDaysLabel()
            . ', ' . $course->start_time . ' - ' . $course->end_time
            . ' | Từ ' . $startDate->format('d/m/Y')
            . ' đến ' . $endDate->format('d/m/Y');
    }

    protected function buildPendingOpenSummary(KhoaHoc $course): string
    {
        return $course->meetingDaysLabel()
            . ', ' . $course->start_time . ' - ' . $course->end_time
            . ' | Chờ đủ ' . KhoaHoc::minimumStudentsToOpen() . ' học viên để mở lớp';
    }

    protected function meetingDaysOverlap(array $sourceDays, array $targetDays): bool
    {
        return array_intersect($sourceDays, $targetDays) !== [];
    }

    protected function ensureStudentHasNoRequestedScheduleConflict(
        int $studentId,
        array $meetingDays,
        string $startTime,
        string $endTime,
        ?int $ignoreEnrollmentId = null
    ): void {
        $startTime = substr($startTime, 0, 5);
        $endTime = substr($endTime, 0, 5);

        $conflict = GhiDanh::query()
            ->with([
                'classRoom.course',
                'classRoom.schedules',
                'course.classRooms.schedules',
            ])
            ->where('user_id', $studentId)
            ->when($ignoreEnrollmentId, fn (Builder $query) => $query->whereKeyNot($ignoreEnrollmentId))
            ->whereIn('status', GhiDanh::scheduleBlockingStatuses())
            ->get()
            ->first(function (GhiDanh $enrollment) use ($meetingDays, $startTime, $endTime): bool {
                $existingClassRoom = $enrollment->conflictReferenceClassRoom();

                if (! $existingClassRoom) {
                    return false;
                }

                foreach ($existingClassRoom->scheduleRows() as $schedule) {
                    if (! in_array((string) ($schedule['day_of_week'] ?? ''), $meetingDays, true)) {
                        continue;
                    }

                    $existingStart = substr((string) ($schedule['start_time'] ?? ''), 0, 5);
                    $existingEnd = substr((string) ($schedule['end_time'] ?? ''), 0, 5);

                    if ($existingStart !== '' && $existingEnd !== '' && $existingStart < $endTime && $existingEnd > $startTime) {
                        return true;
                    }
                }

                return false;
            });

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => 'Học viên đã có lớp khác trùng lịch trong cùng khung giờ.',
            ]);
        }
    }

    protected function queuedStudentCount(KhoaHoc $course): int
    {
        return $course->enrollments()
            ->whereIn('status', GhiDanh::courseAccessStatuses())
            ->count();
    }

    protected function notifyStudentPendingOpen(GhiDanh $enrollment, KhoaHoc $course, int $currentCount): void
    {
        ThongBao::create([
            'user_id' => $enrollment->user_id,
            'title' => 'Lớp đang chờ mở',
            'message' => 'Bạn đã được ghép vào ' . $course->title
                . '. Hiện lớp có ' . $currentCount . '/' . KhoaHoc::minimumStudentsToOpen()
                . ' học viên. Admin sẽ chốt ngày bắt đầu và ngày kết thúc khi lớp đủ người.',
            'type' => 'info',
            'link' => route('student.enroll.my-classes'),
        ]);
    }

    protected function notifyStudentsCourseOpened(KhoaHoc $course): void
    {
        $studentIds = $course->enrollments()
            ->whereIn('status', GhiDanh::courseAccessStatuses())
            ->pluck('user_id')
            ->unique();

        foreach ($studentIds as $studentId) {
            ThongBao::create([
                'user_id' => $studentId,
                'title' => 'Lớp đã được mở',
                'message' => 'Lớp ' . $course->title . ' đã được mở chính thức với lịch '
                    . $course->formattedSchedule() . '.',
                'type' => 'success',
                'link' => route('student.schedule'),
            ]);
        }
    }

    protected function findExistingClassRoomForCourse(int $courseId): ?LopHoc
    {
        return LopHoc::query()
            ->where('course_id', $courseId)
            ->whereNotIn('status', [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED])
            ->orderByDesc('id')
            ->first();
    }

    protected function syncClassRoomForCourse(
        KhoaHoc $course,
        int $roomId,
        int $teacherId,
        array $meetingDays,
        string $startTime,
        string $endTime,
        ?string $startDate = null
    ): LopHoc {
        $classRoom = LopHoc::query()
            ->where('course_id', $course->id)
            ->whereNotIn('status', [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED])
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $payload = [
            'subject_id' => $course->subject_id,
            'course_id' => $course->id,
            'name' => $course->title,
            'room_id' => $roomId,
            'teacher_id' => $teacherId,
            'duration' => $course->subject?->duration,
            'status' => LopHoc::STATUS_OPEN,
        ];

        if ($startDate) {
            $payload['start_date'] = $startDate;
        }

        if (! $classRoom) {
            $classRoom = LopHoc::query()->create($payload);
        } else {
            $classRoom->fill($payload)->save();
        }

        $normalizedMeetingDays = array_values(array_unique(array_filter($meetingDays)));

        $existingSchedules = $classRoom->schedules()
            ->lockForUpdate()
            ->get()
            ->keyBy('day_of_week');

        foreach ($normalizedMeetingDays as $dayOfWeek) {
            $schedule = $existingSchedules->get($dayOfWeek);

            if ($schedule) {
                $schedule->fill([
                    'teacher_id' => $teacherId,
                    'room_id' => $roomId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ])->save();

                continue;
            }

            LichHoc::query()->create([
                'lop_hoc_id' => $classRoom->id,
                'teacher_id' => $teacherId,
                'room_id' => $roomId,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        }

        $classRoom->schedules()
            ->whereNotIn('day_of_week', $normalizedMeetingDays)
            ->delete();

        return $classRoom->fresh(['room', 'schedules']);
    }

    protected function refreshClassStatusByCapacity(LopHoc $classRoom): void
    {
        $classRoom->loadCount('enrollments');
        $classRoom->loadMissing('room');

        if (! $classRoom->room || $classRoom->status === LopHoc::STATUS_COMPLETED || $classRoom->status === LopHoc::STATUS_CLOSED) {
            return;
        }

        $classRoom->update([
            'status' => (int) $classRoom->enrollments_count >= (int) $classRoom->room->capacity
                ? LopHoc::STATUS_FULL
                : LopHoc::STATUS_OPEN,
        ]);
    }
}

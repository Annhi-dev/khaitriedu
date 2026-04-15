<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Room;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminScheduleService
{
    public function queueEnrollments(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return Enrollment::query()
            ->with(['user', 'subject.category'])
            ->whereNull('lop_hoc_id')
            ->whereIn('status', [Enrollment::STATUS_PENDING, Enrollment::STATUS_APPROVED])
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
            ->orderByRaw("case when status = '" . Enrollment::STATUS_APPROVED . "' then 0 else 1 end")
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

        return Course::query()
            ->with(['subject.category', 'teacher', 'enrollments.user', 'classRooms.room', 'classRooms.teacher', 'classRooms.schedules'])
            ->withCount([
                'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
            ])
            ->whereNotNull('teacher_id')
            ->whereIn('status', [
                Course::STATUS_PENDING_OPEN,
                Course::STATUS_SCHEDULED,
                Course::STATUS_ACTIVE,
            ])
            ->when($teacherId, fn (Builder $query) => $query->where('teacher_id', $teacherId))
            ->when($courseId, fn (Builder $query) => $query->whereKey($courseId))
            ->when($studentId, function (Builder $query) use ($studentId) {
                $query->whereHas('enrollments', function (Builder $enrollmentQuery) use ($studentId) {
                    $enrollmentQuery->where('user_id', $studentId)
                        ->whereIn('status', Enrollment::courseAccessStatuses());
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
            ->orderByRaw("case when status = '" . Course::STATUS_PENDING_OPEN . "' then 0 when status = '" . Course::STATUS_SCHEDULED . "' then 1 when status = '" . Course::STATUS_ACTIVE . "' then 2 else 3 end")
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();
    }

    public function getSchedulingContext(Enrollment $enrollment): array
    {
        $enrollment->load(['user', 'subject.category', 'course.teacher']);

        $courses = Course::query()
            ->with(['teacher', 'subject'])
            ->where('subject_id', $enrollment->subject_id)
            ->orderBy('title')
            ->get();

        $waitingCourses = Course::query()
            ->with(['teacher', 'subject'])
            ->withCount([
                'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
            ])
            ->where('subject_id', $enrollment->subject_id)
            ->where('status', Course::STATUS_PENDING_OPEN)
            ->orderBy('title')
            ->get();

        return [
            'enrollment' => $enrollment,
            'courses' => $courses,
            'waitingCourses' => $waitingCourses,
            'teachers' => $this->teacherOptions(),
            'rooms' => $this->roomOptions(),
            'suggestedCourseTitle' => $this->suggestedCourseTitle($enrollment),
            'minimumStudentsToOpen' => Course::minimumStudentsToOpen(),
        ];
    }

    public function getOpenCourseContext(Course $course): array
    {
        $course->load([
            'subject.category',
            'teacher',
            'enrollments.user',
        ])->loadCount([
            'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
        ]);

        return [
            'course' => $course,
            'minimumStudentsToOpen' => Course::minimumStudentsToOpen(),
            'studentsNeeded' => max(0, Course::minimumStudentsToOpen() - (int) $course->scheduled_students_count),
            'rooms' => $this->roomOptions(),
        ];
    }

    public function getCourseDetailContext(Course $course): array
    {
        $course->load([
            'subject.category',
            'teacher',
            'enrollments.user',
            'classRooms.room',
            'classRooms.teacher',
            'classRooms.schedules.room',
        ])->loadCount([
            'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
        ]);

        $classRoom = $course->currentClassRoom();
        $classRoom?->loadMissing(['room', 'teacher', 'schedules.room']);

        $activeEnrollments = $course->enrollments
            ->filter(fn (Enrollment $enrollment) => in_array($enrollment->status, Enrollment::courseAccessStatuses(), true))
            ->sortBy(function (Enrollment $enrollment) {
                return match ($enrollment->status) {
                    Enrollment::STATUS_SCHEDULED => 0,
                    Enrollment::STATUS_ACTIVE => 1,
                    Enrollment::STATUS_APPROVED => 2,
                    Enrollment::STATUS_PENDING => 3,
                    default => 9,
                };
            })
            ->values();

        $classSchedules = $classRoom?->schedules
            ? $classRoom->schedules->sortBy(function (ClassSchedule $schedule) {
                return array_search($schedule->day_of_week, array_keys(ClassSchedule::$dayOptions), true);
            })->values()
            : collect();

        return [
            'course' => $course,
            'classRoom' => $classRoom,
            'classSchedules' => $classSchedules,
            'activeEnrollments' => $activeEnrollments,
            'scheduledStudentsCount' => (int) $course->scheduled_students_count,
            'minimumStudentsToOpen' => Course::minimumStudentsToOpen(),
        ];
    }

    public function scheduleEnrollment(Enrollment $enrollment, array $data, User $admin): string
    {
        return DB::transaction(function () use ($enrollment, $data, $admin): string {
            $enrollment = Enrollment::query()
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
                'status' => Course::STATUS_SCHEDULED,
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
                'status' => Enrollment::STATUS_SCHEDULED,
                'note' => $data['note'] ?? $enrollment->note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ];

            $enrollment->update($enrollmentPayload);
            $course->enrollments()
                ->whereIn('status', Enrollment::courseAccessStatuses())
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

    public function openPendingCourse(Course $course, array $data, User $admin): string
    {
        return DB::transaction(function () use ($course, $data, $admin): string {
            $course = Course::query()
                ->lockForUpdate()
                ->findOrFail($course->id);

            $course->loadMissing(['teacher', 'subject', 'enrollments.user'])->loadCount([
                'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
            ]);

            if (! $course->isPendingOpen()) {
                throw ValidationException::withMessages([
                    'course' => 'Lớp này không ở trạng thái chờ mở.',
                ]);
            }

            if ((int) $course->scheduled_students_count < Course::minimumStudentsToOpen()) {
                throw ValidationException::withMessages([
                    'course' => 'Lớp chưa đủ tối thiểu ' . Course::minimumStudentsToOpen() . ' học viên để mở.',
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
                ->whereIn('status', Enrollment::courseAccessStatuses())
                ->lockForUpdate()
                ->get();

            foreach ($enrollments as $enrollment) {
                $this->ensureStudentAvailability($enrollment, $course->id, $meetingDays, $startDate, $endDate, $startTime, $endTime);
            }

            $course->fill([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => Course::STATUS_SCHEDULED,
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
                ->whereIn('status', Enrollment::courseAccessStatuses())
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
        return User::query()
            ->teachers()
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    public function studentOptions(): Collection
    {
        return User::query()
            ->students()
            ->whereHas('enrollments', fn (Builder $query) => $query->whereIn('status', Enrollment::courseAccessStatuses()))
            ->orderBy('name')
            ->get();
    }

    public function courseOptions(): Collection
    {
        return Course::query()
            ->orderBy('title')
            ->get();
    }

    public function classRoomOptions(): Collection
    {
        return ClassRoom::query()
            ->with(['course.subject', 'room', 'teacher', 'schedules'])
            ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED])
            ->orderByDesc('id')
            ->get();
    }

    public function roomOptions(): Collection
    {
        return Room::query()
            ->where('status', Room::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    protected function savePendingOpenEnrollment(Enrollment $enrollment, Course $course, array $data, User $admin): string
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

        $course->fill([
            'teacher_id' => (int) $teacherId,
            'day_of_week' => $meetingDays[0] ?? null,
            'meeting_days' => $meetingDays,
            'start_date' => null,
            'end_date' => null,
            'start_time' => (string) $startTime,
            'end_time' => (string) $endTime,
            'capacity' => (int) $capacity,
            'status' => Course::STATUS_PENDING_OPEN,
        ]);
        $course->schedule = $this->buildPendingOpenSummary($course);
        $course->save();

        $enrollment->update([
            'course_id' => $course->id,
            'subject_id' => $course->subject_id,
            'assigned_teacher_id' => (int) $teacherId,
            'schedule' => $course->schedule,
            'status' => Enrollment::STATUS_SCHEDULED,
            'note' => $data['note'] ?? $enrollment->note,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $currentCount = $this->queuedStudentCount($course->fresh());
        $this->notifyStudentPendingOpen($enrollment->fresh(['user', 'subject', 'course']), $course->fresh(), $currentCount);

        if ($course->wasRecentlyCreated) {
            return 'Đã lưu lớp chờ mở. Hiện có ' . $currentCount . '/' . Course::minimumStudentsToOpen() . ' học viên.';
        }

        return 'Đã ghép học viên vào lớp chờ mở. Hiện có ' . $currentCount . '/' . Course::minimumStudentsToOpen() . ' học viên.';
    }

    protected function resolveMeetingDays(Course $course, array $data): array
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

    protected function resolveCourse(Enrollment $enrollment, array $data, bool $lockForUpdate = false): Course
    {
        if ($this->shouldCreateNewClass($enrollment)) {
            if (! empty($data['course_id'])) {
                $courseQuery = Course::query();

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

            return new Course([
                'subject_id' => $enrollment->subject_id,
                'title' => $title,
                'description' => $data['new_course_description'] ?? null,
                'status' => Course::STATUS_DRAFT,
            ]);
        }

        if (! empty($data['course_id'])) {
            $courseQuery = Course::query();

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

        return new Course([
            'subject_id' => $enrollment->subject_id,
            'title' => $title,
            'description' => $data['new_course_description'] ?? null,
            'status' => Course::STATUS_DRAFT,
        ]);
    }

    protected function suggestedCourseTitle(Enrollment $enrollment): string
    {
        $subjectName = trim((string) ($enrollment->subject?->name ?? 'Lop hoc'));
        $existingTitles = Course::query()
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

    protected function shouldCreateNewClass(Enrollment $enrollment): bool
    {
        return $enrollment->isCustomScheduleRequest()
            && in_array($enrollment->normalizedStatus(), [
                Enrollment::STATUS_PENDING,
                Enrollment::STATUS_APPROVED,
            ], true);
    }

    protected function ensureTeacherIsValid(int $teacherId): void
    {
        if (! User::query()->teachers()->whereKey($teacherId)->exists()) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Giảng viên được chọn không hợp lệ.',
            ]);
        }
    }

    protected function ensureRoomIsValid(int $roomId): void
    {
        if (! Room::query()->where('status', Room::STATUS_ACTIVE)->whereKey($roomId)->exists()) {
            throw ValidationException::withMessages([
                'room_id' => 'Phòng học được chọn không hợp lệ hoặc đang tạm ngưng.',
            ]);
        }
    }

    protected function ensureTeacherAvailability(?int $excludeCourseId, int $teacherId, array $meetingDays, string $startDate, string $endDate, string $startTime, string $endTime): void
    {
        $conflict = Course::query()
            ->where('teacher_id', $teacherId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereIn('status', Course::schedulingStatuses())
            ->when($excludeCourseId, fn (Builder $query) => $query->whereKeyNot($excludeCourseId))
            ->whereDate('start_date', '<=', $endDate)
            ->where(function (Builder $query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            })
            ->get()
            ->first(fn (Course $course) => $this->meetingDaysOverlap($meetingDays, $course->meetingDayValues()));

        if ($conflict) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Giảng viên đã có lịch trùng vào khung giờ này.',
            ]);
        }
    }

    protected function ensureStudentAvailability(Enrollment $enrollment, ?int $excludeCourseId, array $meetingDays, string $startDate, string $endDate, string $startTime, string $endTime): void
    {
        $conflict = Enrollment::query()
            ->where('user_id', $enrollment->user_id)
            ->whereKeyNot($enrollment->id)
            ->whereIn('status', [Enrollment::STATUS_SCHEDULED, Enrollment::STATUS_ACTIVE])
            ->with('course')
            ->whereHas('course', function (Builder $query) use ($excludeCourseId, $startDate, $endDate, $startTime, $endTime) {
                $query->when($excludeCourseId, fn (Builder $courseQuery) => $courseQuery->whereKeyNot($excludeCourseId))
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime)
                    ->whereIn('status', Course::schedulingStatuses())
                    ->whereDate('start_date', '<=', $endDate)
                    ->where(function (Builder $builder) use ($startDate) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $startDate);
                    });
            })
            ->get()
            ->first(fn (Enrollment $conflictEnrollment) => $conflictEnrollment->course !== null
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
        if (ClassRoom::roomHasConflict($roomId, $meetingDays, $startTime, $endTime, $startDate, $endDate, $excludeClassRoomId)) {
            throw ValidationException::withMessages([
                'room_id' => 'Phòng học đã có lớp khác trùng vào khung giờ này.',
            ]);
        }
    }

    protected function ensureCapacity(Course $course, int $capacity, Enrollment $enrollment): void
    {
        $currentCount = $course->exists
            ? $course->enrollments()
                ->whereIn('status', Enrollment::courseAccessStatuses())
                ->when($enrollment->course_id === $course->id, fn (Builder $query) => $query->whereKeyNot($enrollment->id))
                ->count()
            : 0;

        if ($currentCount + 1 > $capacity) {
            throw ValidationException::withMessages([
                'capacity' => 'Sĩ số lớp học không đủ để xếp thêm học viên này.',
            ]);
        }
    }

    protected function buildScheduleSummary(Course $course): string
    {
        $startDate = $course->start_date instanceof Carbon ? $course->start_date : Carbon::parse($course->start_date);
        $endDate = $course->end_date instanceof Carbon ? $course->end_date : Carbon::parse($course->end_date);

        return $course->meetingDaysLabel()
            . ', ' . $course->start_time . ' - ' . $course->end_time
            . ' | Từ ' . $startDate->format('d/m/Y')
            . ' đến ' . $endDate->format('d/m/Y');
    }

    protected function buildPendingOpenSummary(Course $course): string
    {
        return $course->meetingDaysLabel()
            . ', ' . $course->start_time . ' - ' . $course->end_time
            . ' | Chờ đủ ' . Course::minimumStudentsToOpen() . ' học viên để mở lớp';
    }

    protected function meetingDaysOverlap(array $sourceDays, array $targetDays): bool
    {
        return array_intersect($sourceDays, $targetDays) !== [];
    }

    protected function queuedStudentCount(Course $course): int
    {
        return $course->enrollments()
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->count();
    }

    protected function notifyStudentPendingOpen(Enrollment $enrollment, Course $course, int $currentCount): void
    {
        Notification::create([
            'user_id' => $enrollment->user_id,
            'title' => 'Lớp đang chờ mở',
            'message' => 'Bạn đã được ghép vào ' . $course->title
                . '. Hiện lớp có ' . $currentCount . '/' . Course::minimumStudentsToOpen()
                . ' học viên. Admin sẽ chốt ngày bắt đầu và ngày kết thúc khi lớp đủ người.',
            'type' => 'info',
            'link' => route('student.enroll.my-classes'),
        ]);
    }

    protected function notifyStudentsCourseOpened(Course $course): void
    {
        $studentIds = $course->enrollments()
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->pluck('user_id')
            ->unique();

        foreach ($studentIds as $studentId) {
            Notification::create([
                'user_id' => $studentId,
                'title' => 'Lớp đã được mở',
                'message' => 'Lớp ' . $course->title . ' đã được mở chính thức với lịch '
                    . $course->formattedSchedule() . '.',
                'type' => 'success',
                'link' => route('student.schedule'),
            ]);
        }
    }

    protected function findExistingClassRoomForCourse(int $courseId): ?ClassRoom
    {
        return ClassRoom::query()
            ->where('course_id', $courseId)
            ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED])
            ->orderByDesc('id')
            ->first();
    }

    protected function syncClassRoomForCourse(
        Course $course,
        int $roomId,
        int $teacherId,
        array $meetingDays,
        string $startTime,
        string $endTime,
        ?string $startDate = null
    ): ClassRoom {
        $classRoom = ClassRoom::query()
            ->where('course_id', $course->id)
            ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED])
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
            'status' => ClassRoom::STATUS_OPEN,
        ];

        if ($startDate) {
            $payload['start_date'] = $startDate;
        }

        if (! $classRoom) {
            $classRoom = ClassRoom::query()->create($payload);
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

            ClassSchedule::query()->create([
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

    protected function refreshClassStatusByCapacity(ClassRoom $classRoom): void
    {
        $classRoom->loadCount('enrollments');
        $classRoom->loadMissing('room');

        if (! $classRoom->room || $classRoom->status === ClassRoom::STATUS_COMPLETED || $classRoom->status === ClassRoom::STATUS_CLOSED) {
            return;
        }

        $classRoom->update([
            'status' => (int) $classRoom->enrollments_count >= (int) $classRoom->room->capacity
                ? ClassRoom::STATUS_FULL
                : ClassRoom::STATUS_OPEN,
        ]);
    }
}

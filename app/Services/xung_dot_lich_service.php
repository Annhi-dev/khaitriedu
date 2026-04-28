<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminScheduleConflictService
{
    public function preview(array $filters): array
    {
        $candidate = $this->resolveCandidate($filters);
        $studentConflicts = $this->studentConflicts();
        [$studentConflictStudentCount, $studentConflictPairCount] = $this->summarizeStudentConflicts($studentConflicts);

        if (! $candidate['ready']) {
            return [
                'candidate' => $candidate,
                'teacherConflicts' => collect(),
                'roomConflicts' => collect(),
                'studentConflicts' => $studentConflicts,
                'studentConflictStudentCount' => $studentConflictStudentCount,
                'studentConflictPairCount' => $studentConflictPairCount,
                'studentConflictCount' => $studentConflictPairCount,
                'hasConflicts' => false,
            ];
        }

        $teacherConflicts = $candidate['teacher_id']
            ? $this->teacherConflicts($candidate)
            : collect();
        $roomConflicts = $candidate['room_id']
            ? $this->roomConflicts($candidate)
            : collect();

        return [
            'candidate' => $candidate,
            'teacherConflicts' => $teacherConflicts,
            'roomConflicts' => $roomConflicts,
            'studentConflicts' => $studentConflicts,
            'studentConflictStudentCount' => $studentConflictStudentCount,
            'studentConflictPairCount' => $studentConflictPairCount,
            'studentConflictCount' => $studentConflictPairCount,
            'hasConflicts' => $teacherConflicts->isNotEmpty() || $roomConflicts->isNotEmpty(),
        ];
    }

    public function previewCourse(array $filters): array
    {
        $candidate = $this->resolveCandidate($filters);
        $studentConflicts = collect();

        if (! $candidate['ready']) {
            return [
                'candidate' => $candidate,
                'teacherConflicts' => collect(),
                'roomConflicts' => collect(),
                'studentConflicts' => $studentConflicts,
                'studentConflictStudentCount' => 0,
                'studentConflictPairCount' => 0,
                'studentConflictCount' => 0,
                'hasConflicts' => false,
            ];
        }

        $teacherConflicts = $candidate['teacher_id']
            ? $this->teacherConflicts($candidate)
            : collect();
        $roomConflicts = $candidate['room_id']
            ? $this->roomConflicts($candidate)
            : collect();
        $studentConflicts = $this->candidateStudentConflicts($candidate);
        [$studentConflictStudentCount, $studentConflictPairCount] = $this->summarizeStudentConflicts($studentConflicts);

        return [
            'candidate' => $candidate,
            'teacherConflicts' => $teacherConflicts,
            'roomConflicts' => $roomConflicts,
            'studentConflicts' => $studentConflicts,
            'studentConflictStudentCount' => $studentConflictStudentCount,
            'studentConflictPairCount' => $studentConflictPairCount,
            'studentConflictCount' => $studentConflictPairCount,
            'hasConflicts' => $teacherConflicts->isNotEmpty() || $roomConflicts->isNotEmpty() || $studentConflicts->isNotEmpty(),
        ];
    }

    public function teacherHasConflict(
        int $teacherId,
        array $meetingDays,
        string $startTime,
        string $endTime,
        string $startDate,
        string $endDate,
        ?int $excludeCourseId = null
    ): bool {
        return $this->teacherConflicts([
            'teacher_id' => $teacherId,
            'days' => $meetingDays,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'exclude_course_id' => $excludeCourseId,
        ])->isNotEmpty();
    }

    public function roomHasConflict(
        int $roomId,
        array $meetingDays,
        string $startTime,
        string $endTime,
        string $startDate,
        string $endDate,
        ?int $excludeClassRoomId = null
    ): bool {
        return $this->roomConflicts([
            'room_id' => $roomId,
            'days' => $meetingDays,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'exclude_class_room_id' => $excludeClassRoomId,
        ])->isNotEmpty();
    }

    protected function resolveCandidate(array $filters): array
    {
        $course = null;
        $classRoom = null;

        if (! empty($filters['course_id'])) {
            $course = Course::query()
                ->with([
                    'teacher',
                    'subject.category',
                    'classRooms.room',
                    'classRooms.teacher',
                    'classRooms.schedules.room',
                    'classRooms.schedules.teacher',
                ])
                ->find($filters['course_id']);
        }

        if (! empty($filters['class_room_id'])) {
            $classRoom = ClassRoom::query()
                ->with([
                    'course.teacher',
                    'course.subject.category',
                    'room',
                    'teacher',
                    'schedules.room',
                    'schedules.teacher',
                ])
                ->find($filters['class_room_id']);
        }

        $teacherId = $filters['teacher_id'] ?? null;
        $roomId = $filters['room_id'] ?? null;
        $days = $this->normalizeDays($filters['day_of_week'] ?? []);
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $startTime = $filters['start_time'] ?? null;
        $endTime = $filters['end_time'] ?? null;
        $excludeCourseId = $filters['exclude_course_id'] ?? null;
        $excludeClassRoomId = $filters['exclude_class_room_id'] ?? null;

        if ($classRoom) {
            $teacherId ??= $classRoom->teacher_id;
            $roomId ??= $classRoom->room_id;
            $excludeClassRoomId ??= $classRoom->id;
            $excludeCourseId ??= $classRoom->course_id;

            if ($days === []) {
                $days = $classRoom->schedules
                    ->pluck('day_of_week')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }

            if (! $startTime && $classRoom->schedules->isNotEmpty()) {
                $startTime = (string) $classRoom->schedules->first()->start_time;
            }

            if (! $endTime && $classRoom->schedules->isNotEmpty()) {
                $endTime = (string) $classRoom->schedules->first()->end_time;
            }

            if (! $startDate && $classRoom->start_date) {
                $startDate = $classRoom->start_date->format('Y-m-d');
            }

            if (! $endDate && $classRoom->start_date) {
                $endDate = $classRoom->start_date->copy()
                    ->addMonths(max(1, (int) ($classRoom->duration ?? $classRoom->course?->subject?->duration ?? 1)))
                    ->format('Y-m-d');
            }
        }

        if ($course) {
            $teacherId ??= $course->teacher_id;
            $excludeCourseId ??= $course->id;

            if ($days === []) {
                $days = $course->meetingDayValues();
            }

            if (! $startTime && $course->start_time) {
                $startTime = (string) $course->start_time;
            }

            if (! $endTime && $course->end_time) {
                $endTime = (string) $course->end_time;
            }

            if (! $startDate && $course->start_date) {
                $startDate = $course->start_date->format('Y-m-d');
            }

            if (! $endDate && $course->end_date) {
                $endDate = $course->end_date->format('Y-m-d');
            }

            $courseClassRoom = $course->currentClassRoom();

            if (! $roomId && $courseClassRoom?->room_id) {
                $roomId = (int) $courseClassRoom->room_id;
            }

            if (! $excludeClassRoomId && $courseClassRoom) {
                $excludeClassRoomId = $courseClassRoom->id;
            }
        }

        if (! $startDate && $course?->start_date) {
            $startDate = $course->start_date->format('Y-m-d');
        }

        if (! $endDate && $course?->start_date) {
            $months = max(1, (int) ($course->subject?->duration ?? $classRoom?->duration ?? 1));
            $endDate = $course->start_date->copy()->addMonths($months)->format('Y-m-d');
        }

        $teacher = $teacherId ? User::query()->find($teacherId) : null;
        $room = $roomId ? Room::query()->find($roomId) : null;

        $previewCourse = new Course([
            'day_of_week' => $days[0] ?? null,
            'meeting_days' => $days,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $ready = $days !== []
            && ($teacherId !== null || $roomId !== null)
            && ($startDate !== null)
            && ($endDate !== null)
            && ($startTime !== null)
            && ($endTime !== null);

        return [
            'course' => $course,
            'classRoom' => $classRoom,
            'teacher_id' => $teacherId ? (int) $teacherId : null,
            'room_id' => $roomId ? (int) $roomId : null,
            'days' => $days,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'exclude_course_id' => $excludeCourseId ? (int) $excludeCourseId : null,
            'exclude_class_room_id' => $excludeClassRoomId ? (int) $excludeClassRoomId : null,
            'teacher' => $teacher,
            'room' => $room,
            'source_label' => $this->buildSourceLabel($course, $classRoom),
            'previewCourse' => $previewCourse,
            'meeting_days_label' => $previewCourse->meetingDaysLabel(),
            'schedule_label' => $previewCourse->formattedSchedule(),
            'ready' => $ready,
        ];
    }

    protected function teacherConflicts(array $candidate): Collection
    {
        if (! ($candidate['ready'] ?? true) || ! ($candidate['teacher_id'] ?? null)) {
            return collect();
        }

        $courseConflicts = Course::query()
            ->with([
                'subject.category',
                'teacher',
                'classRooms.room',
                'classRooms.teacher',
                'classRooms.schedules.room',
                'classRooms.schedules.teacher',
            ])
            ->where('teacher_id', $candidate['teacher_id'])
            ->whereIn('status', [
                Course::STATUS_PENDING_OPEN,
                Course::STATUS_SCHEDULED,
                Course::STATUS_ACTIVE,
            ])
            ->when($candidate['exclude_course_id'] ?? null, fn (Builder $builder) => $builder->whereKeyNot($candidate['exclude_course_id']))
            ->where('start_time', '<', $candidate['end_time'])
            ->where('end_time', '>', $candidate['start_time'])
            ->where(function (Builder $builder) use ($candidate) {
                $builder->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $candidate['end_date']);
            })
            ->where(function (Builder $builder) use ($candidate) {
                $builder->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $candidate['start_date']);
            });

        $classRoomConflicts = ClassRoom::query()
            ->with([
                'course.subject.category',
                'course.teacher',
                'course.classRooms.room',
                'course.classRooms.teacher',
                'course.classRooms.schedules.room',
                'course.classRooms.schedules.teacher',
                'room',
                'teacher',
                'schedules.room',
                'schedules.teacher',
            ])
            ->where('teacher_id', $candidate['teacher_id'])
            ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED])
            ->when($candidate['exclude_class_room_id'] ?? null, fn (Builder $builder) => $builder->whereKeyNot($candidate['exclude_class_room_id']))
            ->when($candidate['exclude_course_id'] ?? null, fn (Builder $builder) => $builder->where('course_id', '!=', $candidate['exclude_course_id']))
            ->whereHas('schedules', function (Builder $query) use ($candidate) {
                $query->whereIn('day_of_week', $candidate['days'])
                    ->where('start_time', '<', $candidate['end_time'])
                    ->where('end_time', '>', $candidate['start_time']);
            })
            ->get()
            ->filter(fn (ClassRoom $classRoom) => $classRoom->overlapsDateRange(
                Carbon::parse($candidate['start_date'])->startOfDay(),
                Carbon::parse($candidate['end_date'])->endOfDay(),
            ))
            ->map(fn (ClassRoom $classRoom) => $classRoom->course)
            ->filter();

        return $courseConflicts->get()
            ->filter(fn (Course $course) => $this->meetingDaysOverlap($candidate['days'], $course->meetingDayValues()))
            ->concat($classRoomConflicts)
            ->filter()
            ->unique('id')
            ->values();
    }

    protected function roomConflicts(array $candidate): Collection
    {
        if (! ($candidate['ready'] ?? true) || ! ($candidate['room_id'] ?? null)) {
            return collect();
        }

        $query = ClassRoom::query()
            ->with([
                'subject.category',
                'course.teacher',
                'room',
                'teacher',
                'schedules.room',
                'schedules.teacher',
            ])
            ->where('room_id', $candidate['room_id'])
            ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED])
            ->when($candidate['exclude_class_room_id'] ?? null, fn (Builder $builder) => $builder->whereKeyNot($candidate['exclude_class_room_id']))
            ->whereHas('schedules', function (Builder $query) use ($candidate) {
                $query->whereIn('day_of_week', $candidate['days'])
                    ->where('start_time', '<', $candidate['end_time'])
                    ->where('end_time', '>', $candidate['start_time']);
            });

        return $query->get()
            ->filter(fn (ClassRoom $classRoom) => $classRoom->overlapsDateRange(
                Carbon::parse($candidate['start_date'])->startOfDay(),
                Carbon::parse($candidate['end_date'])->endOfDay(),
            ))
            ->values();
    }

    protected function meetingDaysOverlap(array $sourceDays, array $targetDays): bool
    {
        return array_intersect($sourceDays, $targetDays) !== [];
    }

    public function studentConflicts(): Collection
    {
        $enrollments = Enrollment::query()
            ->with([
                'user',
                'subject.category',
                'course.subject.category',
                'course.classRooms.schedules',
                'classRoom.course.subject.category',
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules',
            ])
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->whereNotNull('lop_hoc_id')
            ->get();

        Enrollment::syncDisplayStatusesByClass($enrollments);

        $rows = $enrollments
            ->groupBy('user_id')
            ->flatMap(function (Collection $studentEnrollments) {
                return $this->buildStudentConflictRows($studentEnrollments);
            });

        return $this->aggregateStudentConflictRows($rows);
    }

    protected function candidateStudentConflicts(array $candidate): Collection
    {
        $course = $candidate['course'] ?? null;
        $classRoom = $candidate['classRoom'] ?? null;
        $targetClassRoom = $classRoom ?: $course?->currentClassRoom();

        if (! $targetClassRoom) {
            return collect();
        }

        $studentIds = $targetClassRoom->enrollments()
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->pluck('user_id');

        if ($studentIds->isEmpty()) {
            return collect();
        }

        $conflictingEnrollments = Enrollment::query()
            ->with([
                'user',
                'course.subject.category',
                'course.classRooms.room',
                'course.classRooms.teacher',
                'course.classRooms.schedules',
            ])
            ->whereIn('user_id', $studentIds)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->whereHas('course', function (Builder $query) use ($candidate) {
                $query->whereIn('status', Course::schedulingStatuses())
                    ->when($candidate['exclude_course_id'] ?? null, fn (Builder $builder) => $builder->whereKeyNot($candidate['exclude_course_id']))
                    ->where('start_time', '<', $candidate['end_time'])
                    ->where('end_time', '>', $candidate['start_time'])
                    ->whereDate('start_date', '<=', $candidate['end_date'])
                    ->where(function (Builder $builder) use ($candidate) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $candidate['start_date']);
                    });
            })
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->course !== null
                && $this->meetingDaysOverlap($candidate['days'], $enrollment->course->meetingDayValues()));

        return $conflictingEnrollments
            ->groupBy('user_id')
            ->map(function (Collection $studentEnrollments) use ($candidate) {
                $student = $studentEnrollments->first()?->user;
                $pairs = $studentEnrollments
                    ->map(function (Enrollment $enrollment) use ($candidate) {
                        $conflictingCourse = $enrollment->course;
                        $conflictingClassRoom = $conflictingCourse?->currentClassRoom();

                        if (! $conflictingCourse) {
                            return null;
                        }

                        return [
                            'enrollment_id' => $enrollment->id,
                            'course_title' => $conflictingCourse->title,
                            'schedule' => $conflictingCourse->formattedSchedule(),
                            'candidate_schedule' => $candidate['schedule_label'] ?? $conflictingCourse->formattedSchedule(),
                            'day_label' => $conflictingCourse->meetingDaysLabel(),
                            'note' => 'Trùng với lịch đang chọn ' . ($candidate['schedule_label'] ?? 'chưa rõ'),
                            'url' => $conflictingClassRoom
                                ? route('admin.classes.show', $conflictingClassRoom)
                                : route('admin.course.show', $conflictingCourse),
                            'edit_url' => $conflictingCourse
                                ? route('admin.course.show', $conflictingCourse)
                                : null,
                            'delete_url' => $conflictingClassRoom && $conflictingClassRoom->enrolledCount() === 0
                                ? route('admin.classes.delete', $conflictingClassRoom)
                                : null,
                        ];
                    })
                    ->filter()
                    ->values();

                if ($pairs->isEmpty()) {
                    return null;
                }

                return [
                    'student_id' => $student?->id,
                    'student_name' => $student?->name ?? 'Chưa rõ',
                    'student_email' => $student?->email ?? '',
                    'student_url' => $student ? route('admin.students.show', $student) : null,
                    'conflict_count' => $pairs->count(),
                    'conflicts' => $pairs,
                ];
            })
            ->filter()
            ->values();
    }

    public function studentConflictPairCount(): int
    {
        return $this->studentConflicts()->count();
    }

    protected function buildStudentConflictRows(Collection $studentEnrollments): Collection
    {
        $student = $studentEnrollments->first()?->user;
        $pairs = collect();
        $classRoomIds = collect();
        $items = $studentEnrollments->values();

        for ($i = 0; $i < $items->count(); $i++) {
            $firstEnrollment = $items[$i];
            $firstClassRoom = $firstEnrollment->conflictReferenceClassRoom();

            if (! $firstClassRoom) {
                continue;
            }

            $classRoomIds->push($firstClassRoom->id);

            for ($j = $i + 1; $j < $items->count(); $j++) {
                $secondEnrollment = $items[$j];
                $secondClassRoom = $secondEnrollment->conflictReferenceClassRoom();

                if (! $secondClassRoom || (int) $firstClassRoom->id === (int) $secondClassRoom->id) {
                    continue;
                }

                $conflict = $firstClassRoom->firstScheduleConflictWith($secondClassRoom);

                if (! $conflict) {
                    continue;
                }

                $pairs->push([
                    'pair_key' => $this->buildStudentConflictPairKey($firstClassRoom, $secondClassRoom, $conflict),
                    'student_id' => $student?->id,
                    'student_name' => $student?->name ?? 'Chưa rõ',
                    'student_email' => $student?->email ?? '',
                    'student_url' => $student ? route('admin.students.show', $student) : null,
                    'first' => [
                        'enrollment_id' => $firstEnrollment->id,
                        'class_room_id' => $firstClassRoom->id,
                        'title' => $firstClassRoom->displayName(),
                        'schedule' => $firstClassRoom->scheduleSummary(),
                        'status' => $firstEnrollment->displayStatusLabel(),
                        'url' => route('admin.classes.show', $firstClassRoom),
                        'edit_url' => $firstClassRoom->course
                            ? route('admin.course.show', $firstClassRoom->course)
                            : route('admin.classes.show', $firstClassRoom),
                        'delete_url' => $firstClassRoom->enrolledCount() === 0
                            ? route('admin.classes.delete', $firstClassRoom)
                            : null,
                    ],
                    'second' => [
                        'enrollment_id' => $secondEnrollment->id,
                        'class_room_id' => $secondClassRoom->id,
                        'title' => $secondClassRoom->displayName(),
                        'schedule' => $secondClassRoom->scheduleSummary(),
                        'status' => $secondEnrollment->displayStatusLabel(),
                        'url' => route('admin.classes.show', $secondClassRoom),
                        'edit_url' => $secondClassRoom->course
                            ? route('admin.course.show', $secondClassRoom->course)
                            : route('admin.classes.show', $secondClassRoom),
                        'delete_url' => $secondClassRoom->enrolledCount() === 0
                            ? route('admin.classes.delete', $secondClassRoom)
                            : null,
                    ],
                    'day_label' => $conflict['day_label'] ?? 'Chưa rõ ngày',
                    'existing_time_label' => $conflict['existing_time_label'] ?? '',
                    'candidate_time_label' => $conflict['candidate_time_label'] ?? '',
                    'note' => sprintf(
                        'Trùng vào %s, %s - %s.',
                        $conflict['day_label'] ?? 'chưa rõ ngày',
                        $conflict['existing_time_label'] ?? '',
                        $conflict['candidate_time_label'] ?? ''
                    ),
                ]);
            }
        }

        if ($pairs->isEmpty()) {
            return collect();
        }

        return $pairs->values();
    }

    protected function aggregateStudentConflictRows(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        return $rows
            ->groupBy('pair_key')
            ->map(function (Collection $group) {
                $first = $group->first();
                $students = $group
                    ->map(fn (array $row) => [
                        'student_id' => $row['student_id'] ?? null,
                        'student_name' => $row['student_name'] ?? 'Chưa rõ',
                        'student_email' => $row['student_email'] ?? '',
                        'student_url' => $row['student_url'] ?? null,
                    ])
                    ->filter(fn (array $student) => $student['student_id'] !== null)
                    ->unique('student_id')
                    ->values();

                if ($students->isEmpty()) {
                    return null;
                }

                $studentNames = $students->pluck('student_name')->filter()->values();

                return [
                    'student_name' => $studentNames->first() ?? 'Chưa rõ',
                    'student_summary' => $this->buildStudentSummary($studentNames),
                    'student_email' => $students->first()['student_email'] ?? '',
                    'student_url' => $students->first()['student_url'] ?? null,
                    'student_count' => $students->count(),
                    'class_count' => 2,
                    'conflict_count' => $students->count(),
                    'students' => $students->all(),
                    'conflicts' => collect([[
                        'first' => $first['first'],
                        'second' => $first['second'],
                        'day_label' => $first['day_label'],
                        'existing_time_label' => $first['existing_time_label'],
                        'candidate_time_label' => $first['candidate_time_label'],
                        'note' => $first['note'],
                    ]]),
                ];
            })
            ->filter()
            ->sortByDesc(fn (array $item) => $item['student_count'])
            ->values();
    }

    protected function summarizeStudentConflicts(Collection $studentConflicts): array
    {
        $studentCount = $studentConflicts
            ->flatMap(function (array $group) {
                if (is_array($group['students'] ?? null)) {
                    return collect($group['students'])->pluck('student_id');
                }

                return isset($group['student_id']) ? [$group['student_id']] : [];
            })
            ->filter()
            ->unique()
            ->count();

        return [
            $studentCount,
            $studentConflicts->count(),
        ];
    }

    protected function buildStudentConflictPairKey(ClassRoom $firstClassRoom, ClassRoom $secondClassRoom, array $conflict): string
    {
        $ids = [(int) $firstClassRoom->id, (int) $secondClassRoom->id];
        sort($ids);

        return implode(':', $ids) . '|' . ($conflict['day_label'] ?? '') . '|' . ($conflict['existing_time_label'] ?? '') . '|' . ($conflict['candidate_time_label'] ?? '');
    }

    protected function buildStudentSummary(Collection $studentNames): string
    {
        if ($studentNames->isEmpty()) {
            return 'Chưa rõ';
        }

        if ($studentNames->count() <= 3) {
            return $studentNames->implode(' • ');
        }

        return $studentNames->take(3)->implode(' • ') . ' + ' . ($studentNames->count() - 3) . ' học viên khác';
    }

    protected function normalizeDays(mixed $days): array
    {
        if (is_string($days) && $days !== '') {
            $days = [$days];
        }

        if (! is_array($days)) {
            return [];
        }

        return collect($days)
            ->map(fn ($day) => is_string($day) ? trim($day) : null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function buildSourceLabel(?Course $course, ?ClassRoom $classRoom): string
    {
        $parts = [];

        if ($course) {
            $parts[] = 'Khóa: ' . $course->title;
        }

        if ($classRoom) {
            $parts[] = 'Lớp: ' . $classRoom->displayName();
        }

        return $parts !== [] ? implode(' | ', $parts) : 'Nhập tay';
    }
}

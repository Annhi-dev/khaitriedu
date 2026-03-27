<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AdminScheduleService
{
    public function queueEnrollments(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return Enrollment::query()
            ->with(['user', 'subject.category'])
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
            ->with(['subject.category', 'teacher', 'enrollments.user'])
            ->withCount([
                'enrollments as scheduled_students_count' => fn (Builder $query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
            ])
            ->whereNotNull('teacher_id')
            ->whereNotNull('day_of_week')
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->when($teacherId, fn (Builder $query) => $query->where('teacher_id', $teacherId))
            ->when($courseId, fn (Builder $query) => $query->whereKey($courseId))
            ->when($studentId, function (Builder $query) use ($studentId) {
                $query->whereHas('enrollments', function (Builder $enrollmentQuery) use ($studentId) {
                    $enrollmentQuery->where('user_id', $studentId)
                        ->whereIn('status', Enrollment::courseAccessStatuses());
                });
            })
            ->when($date, function (Builder $query) use ($date) {
                $query->whereDate('start_date', '<=', $date)
                    ->where(function (Builder $builder) use ($date) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $date);
                    });
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
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

        return [
            'enrollment' => $enrollment,
            'courses' => $courses,
            'teachers' => $this->teacherOptions(),
        ];
    }

    public function scheduleEnrollment(Enrollment $enrollment, array $data, User $admin): string
    {
        $enrollment->loadMissing(['subject', 'user']);
        $course = $this->resolveCourse($enrollment, $data);

        $teacherId = $data['teacher_id'] ?? $course->teacher_id;
        $dayOfWeek = $data['day_of_week'] ?? $course->day_of_week;
        $startDate = $data['start_date'] ?? optional($course->start_date)?->format('Y-m-d');
        $endDate = $data['end_date'] ?? optional($course->end_date)?->format('Y-m-d');
        $startTime = $data['start_time'] ?? $course->start_time;
        $endTime = $data['end_time'] ?? $course->end_time;
        $capacity = $data['capacity'] ?? $course->capacity ?? 20;

        if (! $teacherId) {
            throw ValidationException::withMessages(['teacher_id' => 'Vui lòng chọn giảng viên cho lớp học.']);
        }

        foreach ([
            'day_of_week' => $dayOfWeek,
            'start_date' => $startDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ] as $field => $value) {
            if (! $value) {
                throw ValidationException::withMessages([$field => 'Vui lòng cung cấp đầy đủ thông tin lịch học chính thức.']);
            }
        }

        $this->ensureTeacherIsValid((int) $teacherId);
        $this->ensureTeacherAvailability($course->id, (int) $teacherId, (string) $dayOfWeek, (string) $startDate, $endDate, (string) $startTime, (string) $endTime);
        $this->ensureStudentAvailability($enrollment, $course->id, (string) $dayOfWeek, (string) $startDate, $endDate, (string) $startTime, (string) $endTime);
        $this->ensureCapacity($course, (int) $capacity, $enrollment);

        $course->fill([
            'teacher_id' => (int) $teacherId,
            'day_of_week' => (string) $dayOfWeek,
            'start_date' => $startDate,
            'end_date' => $endDate ?: null,
            'start_time' => (string) $startTime,
            'end_time' => (string) $endTime,
            'capacity' => (int) $capacity,
            'status' => Course::STATUS_SCHEDULED,
        ]);
        $course->schedule = $this->buildScheduleSummary($course);
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

        return $course->wasRecentlyCreated
            ? 'Đã tạo lớp học mới và xếp lịch thành công cho học viên.'
            : 'Đã xếp lịch chính thức cho học viên.';
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

    protected function resolveCourse(Enrollment $enrollment, array $data): Course
    {
        if (! empty($data['course_id'])) {
            $course = Course::query()->findOrFail($data['course_id']);
            if ((int) $course->subject_id !== (int) $enrollment->subject_id) {
                throw ValidationException::withMessages([
                    'course_id' => 'Lớp học được chọn không thuộc đúng khóa học mà học viên đã đăng ký.',
                ]);
            }

            return $course;
        }

        $title = trim((string) ($data['new_course_title'] ?? ''));
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

    protected function ensureTeacherIsValid(int $teacherId): void
    {
        if (! User::query()->teachers()->whereKey($teacherId)->exists()) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Giảng viên được chọn không hợp lệ.',
            ]);
        }
    }

    protected function ensureTeacherAvailability(?int $excludeCourseId, int $teacherId, string $dayOfWeek, string $startDate, ?string $endDate, string $startTime, string $endTime): void
    {
        $targetEndDate = $endDate ?: $startDate;

        $conflict = Course::query()
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereIn('status', Course::schedulingStatuses())
            ->when($excludeCourseId, fn (Builder $query) => $query->whereKeyNot($excludeCourseId))
            ->whereDate('start_date', '<=', $targetEndDate)
            ->where(function (Builder $query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Giảng viên đã có lịch trùng vào khung giờ này.',
            ]);
        }
    }

    protected function ensureStudentAvailability(Enrollment $enrollment, ?int $excludeCourseId, string $dayOfWeek, string $startDate, ?string $endDate, string $startTime, string $endTime): void
    {
        $targetEndDate = $endDate ?: $startDate;

        $conflict = Enrollment::query()
            ->where('user_id', $enrollment->user_id)
            ->whereKeyNot($enrollment->id)
            ->whereIn('status', [Enrollment::STATUS_SCHEDULED, Enrollment::STATUS_ACTIVE])
            ->whereHas('course', function (Builder $query) use ($excludeCourseId, $dayOfWeek, $startDate, $targetEndDate, $startTime, $endTime) {
                $query->when($excludeCourseId, fn (Builder $courseQuery) => $courseQuery->whereKeyNot($excludeCourseId))
                    ->where('day_of_week', $dayOfWeek)
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime)
                    ->whereIn('status', Course::schedulingStatuses())
                    ->whereDate('start_date', '<=', $targetEndDate)
                    ->where(function (Builder $builder) use ($startDate) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $startDate);
                    });
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => 'Học viên đã có lớp khác trùng lịch vào khung giờ này.',
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
        $range = 'Từ ' . $startDate->format('d/m/Y');

        if ($course->end_date) {
            $endDate = $course->end_date instanceof Carbon ? $course->end_date : Carbon::parse($course->end_date);
            $range .= ' đến ' . $endDate->format('d/m/Y');
        }

        return $course->dayLabel() . ', ' . $course->start_time . ' - ' . $course->end_time . ' | ' . $range;
    }
}
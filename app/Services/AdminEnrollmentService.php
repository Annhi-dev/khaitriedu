<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AdminEnrollmentService
{
    public function paginateEnrollments(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return Enrollment::query()
            ->with(['user', 'subject.category', 'course.subject.category', 'assignedTeacher', 'reviewer'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where(function (Builder $userFilter) use ($search) {
                            $userFilter->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                    })->orWhereHas('subject', function (Builder $subjectQuery) use ($search) {
                        $subjectQuery->where('name', 'like', '%' . $search . '%');
                    })->orWhereHas('course', function (Builder $courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', '%' . $search . '%')
                            ->orWhereHas('subject', function (Builder $subjectQuery) use ($search) {
                                $subjectQuery->where('name', 'like', '%' . $search . '%');
                            });
                    });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderByRaw("case when status = '" . Enrollment::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function getEnrollmentDetail(Enrollment $enrollment): array
    {
        $enrollment->load([
            'user',
            'subject.category',
            'course.subject.category',
            'course.teacher',
            'assignedTeacher',
            'reviewer',
        ]);

        $availableCourses = Course::query()
            ->with(['subject.category', 'teacher'])
            ->when($enrollment->subject_id, fn (Builder $query) => $query->where('subject_id', $enrollment->subject_id))
            ->orderBy('title')
            ->get();

        if ($availableCourses->isEmpty()) {
            $availableCourses = Course::query()
                ->with(['subject.category', 'teacher'])
                ->orderBy('title')
                ->get();
        }

        $teachers = User::query()
            ->teachers()
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return [
            'enrollment' => $enrollment,
            'availableCourses' => $availableCourses,
            'teachers' => $teachers,
        ];
    }

    public function reviewEnrollment(Enrollment $enrollment, array $data, User $admin): string
    {
        $action = (string) $data['action'];
        $selectedCourse = $this->resolveSelectedCourse($enrollment, $data['course_id'] ?? null);
        $assignedTeacherId = $data['assigned_teacher_id'] ?? null;
        $note = $data['note'] ?? null;
        $finalSchedule = $data['schedule'] ?? null;

        if ($assignedTeacherId && ! User::query()->teachers()->whereKey($assignedTeacherId)->exists()) {
            throw ValidationException::withMessages([
                'assigned_teacher_id' => 'Giảng viên được chọn không hợp lệ.',
            ]);
        }

        if (! $assignedTeacherId && $selectedCourse?->teacher_id) {
            $assignedTeacherId = $selectedCourse->teacher_id;
        }

        if (($finalSchedule === null || $finalSchedule === '') && $selectedCourse?->schedule) {
            $finalSchedule = $selectedCourse->schedule;
        }

        if (($finalSchedule === null || $finalSchedule === '') && $enrollment->schedule) {
            $finalSchedule = $enrollment->schedule;
        }

        $finalSchedule = $finalSchedule !== '' ? $finalSchedule : null;

        switch ($action) {
            case 'approve':
                $enrollment->status = Enrollment::STATUS_APPROVED;
                $enrollment->course_id = null;
                $enrollment->assigned_teacher_id = null;
                $enrollment->schedule = null;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã được duyệt và đang chờ xếp lớp.';
                break;

            case 'reject':
                $enrollment->status = Enrollment::STATUS_REJECTED;
                $enrollment->course_id = null;
                $enrollment->assigned_teacher_id = null;
                $enrollment->schedule = null;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã bị từ chối.';
                break;

            case 'request_update':
                $enrollment->status = Enrollment::STATUS_PENDING;
                $enrollment->course_id = null;
                $enrollment->assigned_teacher_id = null;
                $enrollment->schedule = null;
                $enrollment->note = $note;
                $message = 'Đã gửi yêu cầu học viên bổ sung lại thông tin đăng ký.';
                break;

            case 'schedule':
                if (! $selectedCourse) {
                    throw ValidationException::withMessages([
                        'course_id' => 'Vui lòng chọn lớp học trước khi xếp lịch.',
                    ]);
                }

                if (! $finalSchedule) {
                    throw ValidationException::withMessages([
                        'schedule' => 'Vui lòng nhập lịch học chính thức hoặc chọn lớp đã có lịch.',
                    ]);
                }

                $enrollment->status = Enrollment::STATUS_SCHEDULED;
                $enrollment->course_id = $selectedCourse->id;
                $enrollment->subject_id = $selectedCourse->subject_id;
                $enrollment->assigned_teacher_id = $assignedTeacherId;
                $enrollment->schedule = $finalSchedule;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã được xếp lớp và chốt lịch sơ bộ.';
                break;

            case 'activate':
                if (! $selectedCourse) {
                    throw ValidationException::withMessages([
                        'course_id' => 'Vui lòng chọn lớp học trước khi chuyển sang đang học.',
                    ]);
                }

                $enrollment->status = Enrollment::STATUS_ACTIVE;
                $enrollment->course_id = $selectedCourse->id;
                $enrollment->subject_id = $selectedCourse->subject_id;
                $enrollment->assigned_teacher_id = $assignedTeacherId;
                $enrollment->schedule = $finalSchedule;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã được chuyển sang trạng thái đang học.';
                break;

            case 'complete':
                if (! $selectedCourse) {
                    throw ValidationException::withMessages([
                        'course_id' => 'Vui lòng chọn lớp học trước khi đánh dấu hoàn thành.',
                    ]);
                }

                $enrollment->status = Enrollment::STATUS_COMPLETED;
                $enrollment->course_id = $selectedCourse->id;
                $enrollment->subject_id = $selectedCourse->subject_id;
                $enrollment->assigned_teacher_id = $assignedTeacherId;
                $enrollment->schedule = $finalSchedule;
                $enrollment->note = $note;
                $message = 'Đăng ký học đã được đánh dấu hoàn thành.';
                break;

            default:
                throw ValidationException::withMessages([
                    'action' => 'Hành động xử lý đăng ký không hợp lệ.',
                ]);
        }

        $enrollment->reviewed_by = $admin->id;
        $enrollment->reviewed_at = now();
        $enrollment->save();

        return $message;
    }

    protected function resolveSelectedCourse(Enrollment $enrollment, ?int $courseId): ?Course
    {
        $course = null;

        if ($courseId) {
            $course = Course::query()->with('teacher')->find($courseId);
        } elseif ($enrollment->course_id) {
            $course = Course::query()->with('teacher')->find($enrollment->course_id);
        }

        if (! $course) {
            return null;
        }

        if ($enrollment->subject_id && (int) $course->subject_id !== (int) $enrollment->subject_id) {
            throw ValidationException::withMessages([
                'course_id' => 'Lớp học phải thuộc đúng khóa học mà học viên đã đăng ký.',
            ]);
        }

        return $course;
    }
}
<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function studentPageData(User $student): array
    {
        return [
            'availableEnrollments' => $this->availableEnrollmentsForStudent($student),
            'recentRequests' => $this->studentRecentRequests($student),
        ];
    }

    public function studentRequests(User $student): LengthAwarePaginator
    {
        return LeaveRequest::query()
            ->where('student_id', $student->id)
            ->with([
                'teacher',
                'course.subject',
                'classRoom.room',
                'classRoom.schedules',
                'classSchedule',
                'reviewer',
            ])
            ->latest('attendance_date')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();
    }

    public function studentRecentRequests(User $student): Collection
    {
        return LeaveRequest::query()
            ->where('student_id', $student->id)
            ->with([
                'teacher',
                'course.subject',
                'classRoom.room',
                'classRoom.schedules',
                'classSchedule',
                'reviewer',
            ])
            ->latest('attendance_date')
            ->latest('id')
            ->take(5)
            ->get();
    }

    public function availableEnrollmentsForStudent(User $student): Collection
    {
        return Enrollment::query()
            ->where('user_id', $student->id)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->whereNotNull('lop_hoc_id')
            ->with(['course.subject', 'assignedTeacher', 'classRoom.room', 'classRoom.schedules', 'classRoom.teacher'])
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->classRoom !== null)
            ->values();
    }

    public function createRequest(User $student, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($student, $data): LeaveRequest {
            $classRoom = ClassRoom::query()
                ->with(['teacher', 'course.teacher', 'course.subject', 'schedules'])
                ->findOrFail($data['class_room_id']);

            $enrollment = Enrollment::query()
                ->where('user_id', $student->id)
                ->where('lop_hoc_id', $classRoom->id)
                ->whereIn('status', Enrollment::courseAccessStatuses())
                ->with(['course.teacher', 'course.subject', 'assignedTeacher', 'classRoom.teacher'])
                ->first();

            if (! $enrollment) {
                throw ValidationException::withMessages([
                    'class_room_id' => 'Bạn chỉ có thể xin phép cho lớp học mình đang tham gia.',
                ]);
            }

            $teacherId = $classRoom->teacher_id
                ?: $enrollment->assigned_teacher_id
                ?: $classRoom->course?->teacher_id;

            if (! $teacherId) {
                throw ValidationException::withMessages([
                    'class_room_id' => 'Lớp học này chưa có giảng viên phụ trách để tiếp nhận xin phép.',
                ]);
            }

            $attendanceDate = Carbon::parse($data['attendance_date'])->startOfDay();
            $classSchedule = $this->resolveScheduleForDate($classRoom, $attendanceDate);

            $leaveRequest = LeaveRequest::create([
                'student_id' => $student->id,
                'teacher_id' => $teacherId,
                'enrollment_id' => $enrollment->id,
                'course_id' => $classRoom->course_id ?: $enrollment->course_id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $classSchedule?->id,
                'attendance_date' => $attendanceDate->toDateString(),
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
                'status' => LeaveRequest::STATUS_PENDING,
            ]);

            $this->notifyTeacherOnSubmission($leaveRequest->fresh([
                'student',
                'classRoom.teacher',
                'classRoom.course.teacher',
                'course.teacher',
                'classSchedule',
                'enrollment.assignedTeacher',
            ]));

            return $leaveRequest;
        });
    }

    public function teacherPageData(User $teacher, array $filters): array
    {
        return [
            'requests' => $this->teacherRequests($teacher, $filters),
        ];
    }

    public function teacherRequests(User $teacher, array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? null;

        return LeaveRequest::query()
            ->where('teacher_id', $teacher->id)
            ->with([
                'student',
                'classRoom.room',
                'classRoom.schedules',
                'classSchedule',
                'reviewer',
            ])
            ->when(in_array($status, array_keys(LeaveRequest::statusOptions()), true), fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('student', function (Builder $studentQuery) use ($search) {
                        $studentQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhereHas('classRoom', function (Builder $classQuery) use ($search) {
                        $classQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'))
                            ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->where('title', 'like', '%' . $search . '%'));
                    })->orWhere('reason', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("case when status = '" . LeaveRequest::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
    }

    public function reviewRequest(LeaveRequest $leaveRequest, User $teacher, array $data): string
    {
        return DB::transaction(function () use ($leaveRequest, $teacher, $data): string {
            $leaveRequest = LeaveRequest::query()
                ->lockForUpdate()
                ->with(['student', 'classRoom.teacher', 'classSchedule', 'enrollment'])
                ->findOrFail($leaveRequest->id);

            $this->assertTeacherOwnership($leaveRequest, $teacher);

            $status = $data['status'];
            $previousStatus = $leaveRequest->status;

            $leaveRequest->update([
                'status' => $status,
                'teacher_note' => $data['teacher_note'] ?? null,
                'reviewed_by' => $teacher->id,
                'reviewed_at' => now(),
            ]);

            $this->syncAttendanceForReview($leaveRequest->fresh(['student', 'classRoom.teacher', 'classRoom.course.teacher', 'course.teacher', 'classSchedule', 'enrollment.assignedTeacher']));

            $this->notifyStudentOnReview($leaveRequest->fresh(['student', 'classRoom', 'classSchedule', 'reviewer']));

            if ($previousStatus === $status) {
                return 'Đã cập nhật ghi chú xử lý yêu cầu xin phép nghỉ.';
            }

            return match ($status) {
                LeaveRequest::STATUS_ACCEPTED => 'Đã chấp nhận yêu cầu xin phép nghỉ.',
                LeaveRequest::STATUS_REJECTED => 'Đã từ chối yêu cầu xin phép nghỉ.',
                LeaveRequest::STATUS_ACKNOWLEDGED => 'Đã ghi nhận yêu cầu xin phép nghỉ.',
                default => 'Đã cập nhật yêu cầu xin phép nghỉ.',
            };
        });
    }

    public function findForStudent(User $student, LeaveRequest $leaveRequest): LeaveRequest
    {
        $leaveRequest->loadMissing(['student', 'teacher', 'course.subject', 'classRoom.teacher', 'classRoom.schedules', 'classSchedule', 'reviewer']);

        abort_if($leaveRequest->student_id !== $student->id, 404);

        return $leaveRequest;
    }

    public function findForTeacher(User $teacher, LeaveRequest $leaveRequest): LeaveRequest
    {
        $leaveRequest->loadMissing(['student', 'teacher', 'course.subject', 'classRoom.teacher', 'classRoom.schedules', 'classSchedule', 'reviewer']);

        $this->assertTeacherOwnership($leaveRequest, $teacher);

        return $leaveRequest;
    }

    protected function resolveScheduleForDate(ClassRoom $classRoom, Carbon $attendanceDate): ?ClassSchedule
    {
        if ($classRoom->schedules->isEmpty()) {
            return null;
        }

        $dayOfWeek = $attendanceDate->englishDayOfWeek;

        return $classRoom->schedules
            ->first(fn ($schedule) => $schedule->day_of_week === $dayOfWeek)
            ?: $classRoom->schedules->first();
    }

    protected function assertTeacherOwnership(LeaveRequest $leaveRequest, User $teacher): void
    {
        $ownsRequest = $leaveRequest->teacher_id === $teacher->id
            || $leaveRequest->classRoom?->teacher_id === $teacher->id;

        abort_unless($ownsRequest, 404);
    }

    protected function syncAttendanceForReview(LeaveRequest $leaveRequest): void
    {
        if (! $leaveRequest->attendance_date || ! $leaveRequest->student_id || ! $leaveRequest->class_room_id) {
            return;
        }

        $query = AttendanceRecord::query()
            ->where('student_id', $leaveRequest->student_id)
            ->where('class_room_id', $leaveRequest->class_room_id)
            ->whereDate('attendance_date', $leaveRequest->attendance_date->toDateString());

        if ($leaveRequest->class_schedule_id) {
            $query->where('class_schedule_id', $leaveRequest->class_schedule_id);
        }

        $attendanceStatus = match ($leaveRequest->status) {
            LeaveRequest::STATUS_ACCEPTED, LeaveRequest::STATUS_ACKNOWLEDGED => AttendanceRecord::STATUS_EXCUSED,
            LeaveRequest::STATUS_REJECTED => AttendanceRecord::STATUS_ABSENT,
            default => null,
        };

        if ($attendanceStatus === null) {
            return;
        }

        $query->update([
            'status' => $attendanceStatus,
            'teacher_id' => $leaveRequest->teacher_id,
        ]);
    }

    protected function notifyTeacherOnSubmission(LeaveRequest $leaveRequest): void
    {
        $recipientIds = collect([
            $leaveRequest->teacher_id,
            $leaveRequest->enrollment?->assigned_teacher_id,
            $leaveRequest->classRoom?->teacher_id,
            $leaveRequest->classRoom?->course?->teacher_id,
            $leaveRequest->course?->teacher_id,
        ])
            ->filter()
            ->map(fn ($teacherId) => (int) $teacherId)
            ->unique()
            ->values();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $message = 'Học viên ' . ($leaveRequest->student?->displayName() ?? 'chưa xác định')
            . ' đã gửi xin phép cho lớp ' . $leaveRequest->targetLabel()
            . ' vào ngày ' . $leaveRequest->attendance_date->format('d/m/Y') . '.';

        foreach ($recipientIds as $teacherId) {
            Notification::create([
                'user_id' => $teacherId,
                'title' => 'Có yêu cầu xin phép nghỉ mới',
                'message' => $message,
                'type' => 'info',
                'link' => route('teacher.leave-requests.show', $leaveRequest),
            ]);
        }
    }

    protected function notifyStudentOnReview(LeaveRequest $leaveRequest): void
    {
        Notification::create([
            'user_id' => $leaveRequest->student_id,
            'title' => match ($leaveRequest->status) {
                LeaveRequest::STATUS_ACCEPTED => 'Yêu cầu xin phép đã được chấp nhận',
                LeaveRequest::STATUS_REJECTED => 'Yêu cầu xin phép bị từ chối',
                LeaveRequest::STATUS_ACKNOWLEDGED => 'Yêu cầu xin phép đã được ghi nhận',
                default => 'Yêu cầu xin phép đã được xử lý',
            },
            'message' => match ($leaveRequest->status) {
                LeaveRequest::STATUS_ACCEPTED => 'Giảng viên đã chấp nhận yêu cầu xin phép của bạn cho lớp ' . $leaveRequest->targetLabel() . '.',
                LeaveRequest::STATUS_REJECTED => 'Giảng viên đã từ chối yêu cầu xin phép của bạn cho lớp ' . $leaveRequest->targetLabel() . '.',
                LeaveRequest::STATUS_ACKNOWLEDGED => 'Giảng viên đã ghi nhận yêu cầu xin phép của bạn cho lớp ' . $leaveRequest->targetLabel() . '.',
                default => 'Yêu cầu xin phép của bạn đã được cập nhật.',
            },
            'type' => in_array($leaveRequest->status, [LeaveRequest::STATUS_REJECTED], true) ? 'warning' : 'success',
            'link' => route('student.leave-requests.show', $leaveRequest),
        ]);
    }
}

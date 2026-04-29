<?php

namespace App\Services;

use App\Models\DiemDanh;
use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\GhiDanh;
use App\Models\YeuCauXinPhep;
use App\Models\ThongBao;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function studentPageData(NguoiDung $student): array
    {
        return [
            'availableEnrollments' => $this->availableEnrollmentsForStudent($student),
            'recentRequests' => $this->studentRecentRequests($student),
        ];
    }

    public function studentRequests(NguoiDung $student): LengthAwarePaginator
    {
        return YeuCauXinPhep::query()
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

    public function studentRecentRequests(NguoiDung $student): Collection
    {
        return YeuCauXinPhep::query()
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

    public function availableEnrollmentsForStudent(NguoiDung $student): Collection
    {
        return GhiDanh::query()
            ->where('user_id', $student->id)
            ->whereIn('status', GhiDanh::courseAccessStatuses())
            ->whereNotNull('lop_hoc_id')
            ->with(['course.subject', 'assignedTeacher', 'classRoom.room', 'classRoom.schedules', 'classRoom.teacher'])
            ->orderByDesc('id')
            ->get()
            ->filter(fn (GhiDanh $enrollment) => $enrollment->classRoom !== null)
            ->values();
    }

    public function createRequest(NguoiDung $student, array $data): YeuCauXinPhep
    {
        return DB::transaction(function () use ($student, $data): YeuCauXinPhep {
            $classRoom = LopHoc::query()
                ->with(['teacher', 'course.teacher', 'course.subject', 'schedules'])
                ->findOrFail($data['class_room_id']);

            $enrollment = GhiDanh::query()
                ->where('user_id', $student->id)
                ->where('lop_hoc_id', $classRoom->id)
                ->whereIn('status', GhiDanh::courseAccessStatuses())
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

            $leaveRequest = YeuCauXinPhep::create([
                'student_id' => $student->id,
                'teacher_id' => $teacherId,
                'enrollment_id' => $enrollment->id,
                'course_id' => $classRoom->course_id ?: $enrollment->course_id,
                'class_room_id' => $classRoom->id,
                'class_schedule_id' => $classSchedule?->id,
                'attendance_date' => $attendanceDate->toDateString(),
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
                'status' => YeuCauXinPhep::STATUS_PENDING,
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

    public function teacherPageData(NguoiDung $teacher, array $filters): array
    {
        return [
            'requests' => $this->teacherRequests($teacher, $filters),
        ];
    }

    public function teacherRequests(NguoiDung $teacher, array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? null;

        return YeuCauXinPhep::query()
            ->where('teacher_id', $teacher->id)
            ->with([
                'student',
                'classRoom.room',
                'classRoom.schedules',
                'classSchedule',
                'reviewer',
            ])
            ->when(in_array($status, array_keys(YeuCauXinPhep::statusOptions()), true), fn (Builder $query) => $query->where('status', $status))
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
            ->orderByRaw("case when status = '" . YeuCauXinPhep::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
    }

    public function reviewRequest(YeuCauXinPhep $leaveRequest, NguoiDung $teacher, array $data): string
    {
        return DB::transaction(function () use ($leaveRequest, $teacher, $data): string {
            $leaveRequest = YeuCauXinPhep::query()
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
                YeuCauXinPhep::STATUS_ACCEPTED => 'Đã chấp nhận yêu cầu xin phép nghỉ.',
                YeuCauXinPhep::STATUS_REJECTED => 'Đã từ chối yêu cầu xin phép nghỉ.',
                YeuCauXinPhep::STATUS_ACKNOWLEDGED => 'Đã ghi nhận yêu cầu xin phép nghỉ.',
                default => 'Đã cập nhật yêu cầu xin phép nghỉ.',
            };
        });
    }

    public function findForStudent(NguoiDung $student, YeuCauXinPhep $leaveRequest): YeuCauXinPhep
    {
        $leaveRequest->loadMissing(['student', 'teacher', 'course.subject', 'classRoom.teacher', 'classRoom.schedules', 'classSchedule', 'reviewer']);

        abort_if($leaveRequest->student_id !== $student->id, 404);

        return $leaveRequest;
    }

    public function findForTeacher(NguoiDung $teacher, YeuCauXinPhep $leaveRequest): YeuCauXinPhep
    {
        $leaveRequest->loadMissing(['student', 'teacher', 'course.subject', 'classRoom.teacher', 'classRoom.schedules', 'classSchedule', 'reviewer']);

        $this->assertTeacherOwnership($leaveRequest, $teacher);

        return $leaveRequest;
    }

    protected function resolveScheduleForDate(LopHoc $classRoom, Carbon $attendanceDate): ?LichHoc
    {
        if ($classRoom->schedules->isEmpty()) {
            return null;
        }

        $dayOfWeek = $attendanceDate->englishDayOfWeek;

        return $classRoom->schedules
            ->first(fn ($schedule) => $schedule->day_of_week === $dayOfWeek)
            ?: $classRoom->schedules->first();
    }

    protected function assertTeacherOwnership(YeuCauXinPhep $leaveRequest, NguoiDung $teacher): void
    {
        $ownsRequest = $leaveRequest->teacher_id === $teacher->id
            || $leaveRequest->classRoom?->teacher_id === $teacher->id;

        abort_unless($ownsRequest, 404);
    }

    protected function syncAttendanceForReview(YeuCauXinPhep $leaveRequest): void
    {
        if (! $leaveRequest->attendance_date || ! $leaveRequest->student_id || ! $leaveRequest->class_room_id) {
            return;
        }

        $query = DiemDanh::query()
            ->where('student_id', $leaveRequest->student_id)
            ->where('class_room_id', $leaveRequest->class_room_id)
            ->whereDate('attendance_date', $leaveRequest->attendance_date->toDateString());

        if ($leaveRequest->class_schedule_id) {
            $query->where('class_schedule_id', $leaveRequest->class_schedule_id);
        }

        $attendanceStatus = match ($leaveRequest->status) {
            YeuCauXinPhep::STATUS_ACCEPTED, YeuCauXinPhep::STATUS_ACKNOWLEDGED => DiemDanh::STATUS_EXCUSED,
            YeuCauXinPhep::STATUS_REJECTED => DiemDanh::STATUS_ABSENT,
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

    protected function notifyTeacherOnSubmission(YeuCauXinPhep $leaveRequest): void
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
            ThongBao::create([
                'user_id' => $teacherId,
                'title' => 'Có yêu cầu xin phép nghỉ mới',
                'message' => $message,
                'type' => 'info',
                'link' => route('teacher.leave-requests.show', $leaveRequest),
            ]);
        }
    }

    protected function notifyStudentOnReview(YeuCauXinPhep $leaveRequest): void
    {
        ThongBao::create([
            'user_id' => $leaveRequest->student_id,
            'title' => match ($leaveRequest->status) {
                YeuCauXinPhep::STATUS_ACCEPTED => 'Yêu cầu xin phép đã được chấp nhận',
                YeuCauXinPhep::STATUS_REJECTED => 'Yêu cầu xin phép bị từ chối',
                YeuCauXinPhep::STATUS_ACKNOWLEDGED => 'Yêu cầu xin phép đã được ghi nhận',
                default => 'Yêu cầu xin phép đã được xử lý',
            },
            'message' => match ($leaveRequest->status) {
                YeuCauXinPhep::STATUS_ACCEPTED => 'Giảng viên đã chấp nhận yêu cầu xin phép của bạn cho lớp ' . $leaveRequest->targetLabel() . '.',
                YeuCauXinPhep::STATUS_REJECTED => 'Giảng viên đã từ chối yêu cầu xin phép của bạn cho lớp ' . $leaveRequest->targetLabel() . '.',
                YeuCauXinPhep::STATUS_ACKNOWLEDGED => 'Giảng viên đã ghi nhận yêu cầu xin phép của bạn cho lớp ' . $leaveRequest->targetLabel() . '.',
                default => 'Yêu cầu xin phép của bạn đã được cập nhật.',
            },
            'type' => in_array($leaveRequest->status, [YeuCauXinPhep::STATUS_REJECTED], true) ? 'warning' : 'success',
            'link' => route('student.leave-requests.show', $leaveRequest),
        ]);
    }
}

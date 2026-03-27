<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AdminScheduleChangeRequestService
{
    public function paginateRequests(array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        return ScheduleChangeRequest::query()
            ->with(['teacher', 'course.subject.category', 'reviewer'])
            ->when(in_array($status, ScheduleChangeRequest::filterableStatuses(), true), fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('teacher', function (Builder $teacherQuery) use ($search) {
                        $teacherQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhereHas('course', function (Builder $courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', '%' . $search . '%')
                            ->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'));
                    })->orWhere('reason', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("case when status = '" . ScheduleChangeRequest::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();
    }

    public function review(ScheduleChangeRequest $scheduleChangeRequest, array $data, User $admin): string
    {
        $scheduleChangeRequest->loadMissing(['teacher', 'course.enrollments.user']);

        if (! $scheduleChangeRequest->isPending()) {
            throw ValidationException::withMessages([
                'action' => 'Yêu cầu này đã được xử lý trước đó.',
            ]);
        }

        if (! $scheduleChangeRequest->course) {
            throw ValidationException::withMessages([
                'action' => 'Lớp học gắn với yêu cầu đổi lịch không còn tồn tại.',
            ]);
        }

        if ($data['action'] === 'approve') {
            $this->ensureTeacherAvailability($scheduleChangeRequest);
            $this->ensureStudentsAvailability($scheduleChangeRequest);
            $newSchedule = $this->applyApprovedSchedule($scheduleChangeRequest);

            $scheduleChangeRequest->update([
                'status' => ScheduleChangeRequest::STATUS_APPROVED,
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return 'Đã duyệt yêu cầu đổi lịch và cập nhật lịch mới: ' . $newSchedule;
        }

        $scheduleChangeRequest->update([
            'status' => ScheduleChangeRequest::STATUS_REJECTED,
            'admin_note' => $data['admin_note'],
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return 'Đã từ chối yêu cầu đổi lịch của giảng viên.';
    }

    protected function applyApprovedSchedule(ScheduleChangeRequest $scheduleChangeRequest): string
    {
        $course = $scheduleChangeRequest->course;
        $newSchedule = $this->buildScheduleLabel($scheduleChangeRequest);

        $course->fill([
            'day_of_week' => $scheduleChangeRequest->requested_day_of_week,
            'start_date' => optional($scheduleChangeRequest->requested_date)->format('Y-m-d'),
            'end_date' => optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d'),
            'start_time' => $scheduleChangeRequest->requested_start_time,
            'end_time' => $scheduleChangeRequest->requested_end_time,
            'schedule' => $newSchedule,
        ]);
        $course->save();

        $course->enrollments()
            ->whereIn('status', [
                Enrollment::LEGACY_STATUS_CONFIRMED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
            ])
            ->update(['schedule' => $newSchedule]);

        return $newSchedule;
    }

    protected function ensureTeacherAvailability(ScheduleChangeRequest $scheduleChangeRequest): void
    {
        $course = $scheduleChangeRequest->course;
        $targetStartDate = optional($scheduleChangeRequest->requested_date)->format('Y-m-d');
        $targetEndDate = optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d') ?: $targetStartDate;

        $conflict = Course::query()
            ->where('teacher_id', $course->teacher_id)
            ->whereKeyNot($course->id)
            ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
            ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
            ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
            ->whereIn('status', Course::schedulingStatuses())
            ->whereDate('start_date', '<=', $targetEndDate)
            ->where(function (Builder $query) use ($targetStartDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $targetStartDate);
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Lịch đề xuất đang trùng với lớp khác mà giảng viên này phụ trách.',
            ]);
        }
    }

    protected function ensureStudentsAvailability(ScheduleChangeRequest $scheduleChangeRequest): void
    {
        $course = $scheduleChangeRequest->course;
        $studentIds = $course->enrollments()
            ->whereIn('status', [
                Enrollment::LEGACY_STATUS_CONFIRMED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
            ])
            ->pluck('user_id');

        if ($studentIds->isEmpty()) {
            return;
        }

        $targetStartDate = optional($scheduleChangeRequest->requested_date)->format('Y-m-d');
        $targetEndDate = optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d') ?: $targetStartDate;

        $conflict = Enrollment::query()
            ->with('user')
            ->whereIn('user_id', $studentIds)
            ->whereIn('status', [
                Enrollment::LEGACY_STATUS_CONFIRMED,
                Enrollment::STATUS_SCHEDULED,
                Enrollment::STATUS_ACTIVE,
            ])
            ->whereHas('course', function (Builder $query) use ($course, $scheduleChangeRequest, $targetStartDate, $targetEndDate) {
                $query->whereKeyNot($course->id)
                    ->where('day_of_week', $scheduleChangeRequest->requested_day_of_week)
                    ->where('start_time', '<', $scheduleChangeRequest->requested_end_time)
                    ->where('end_time', '>', $scheduleChangeRequest->requested_start_time)
                    ->whereIn('status', Course::schedulingStatuses())
                    ->whereDate('start_date', '<=', $targetEndDate)
                    ->where(function (Builder $builder) use ($targetStartDate) {
                        $builder->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $targetStartDate);
                    });
            })
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'action' => 'Lịch đề xuất sẽ làm học viên ' . ($conflict->user?->name ?? 'trong lớp') . ' bị trùng lịch với lớp khác.',
            ]);
        }
    }

    protected function buildScheduleLabel(ScheduleChangeRequest $scheduleChangeRequest): string
    {
        $preview = new Course([
            'day_of_week' => $scheduleChangeRequest->requested_day_of_week,
            'start_date' => optional($scheduleChangeRequest->requested_date)->format('Y-m-d'),
            'end_date' => optional($scheduleChangeRequest->requested_end_date)->format('Y-m-d'),
            'start_time' => $scheduleChangeRequest->requested_start_time,
            'end_time' => $scheduleChangeRequest->requested_end_time,
        ]);

        return $preview->formattedSchedule();
    }
}
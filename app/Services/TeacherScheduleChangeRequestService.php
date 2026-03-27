<?php

namespace App\Services;

use App\Models\Course;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class TeacherScheduleChangeRequestService
{
    public function paginateRequests(User $teacher, array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        return ScheduleChangeRequest::query()
            ->with(['course.subject.category', 'reviewer'])
            ->where('teacher_id', $teacher->id)
            ->when(in_array($status, ScheduleChangeRequest::filterableStatuses(), true), fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('course', function (Builder $courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', '%' . $search . '%')
                            ->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'));
                    })->orWhere('reason', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("case when status = '" . ScheduleChangeRequest::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
    }

    public function createRequest(Course $course, User $teacher, array $data): ScheduleChangeRequest
    {
        if ((int) $course->teacher_id !== (int) $teacher->id) {
            throw ValidationException::withMessages([
                'course' => 'Bạn không có quyền gửi yêu cầu đổi lịch cho lớp học này.',
            ]);
        }

        if (! in_array($course->status, Course::schedulingStatuses(), true)) {
            throw ValidationException::withMessages([
                'course' => 'Chỉ lớp đã có lịch chính thức mới được gửi yêu cầu đổi lịch.',
            ]);
        }

        foreach (['day_of_week', 'start_date', 'start_time', 'end_time'] as $attribute) {
            if (! $course->{$attribute}) {
                throw ValidationException::withMessages([
                    'course' => 'Lớp học này chưa có đủ thông tin lịch hiện tại để tạo yêu cầu đổi lịch.',
                ]);
            }
        }

        if (ScheduleChangeRequest::query()->pending()->where('teacher_id', $teacher->id)->where('course_id', $course->id)->exists()) {
            throw ValidationException::withMessages([
                'course' => 'Lớp học này đang có một yêu cầu đổi lịch chờ admin xử lý.',
            ]);
        }

        $requestedEndDate = $data['requested_end_date'] ?? optional($course->end_date)->format('Y-m-d');
        $currentSchedule = $course->formattedSchedule();
        $requestedSchedule = $this->buildScheduleLabel(
            $data['requested_day_of_week'],
            $data['requested_start_time'],
            $data['requested_end_time'],
            $data['requested_date'],
            $requestedEndDate,
        );

        if ($requestedSchedule === $currentSchedule) {
            throw ValidationException::withMessages([
                'requested_start_time' => 'Lịch đề xuất đang trùng với lịch hiện tại của lớp học.',
            ]);
        }

        return ScheduleChangeRequest::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'current_schedule' => $currentSchedule,
            'requested_day_of_week' => $data['requested_day_of_week'],
            'requested_date' => $data['requested_date'],
            'requested_end_date' => $requestedEndDate,
            'requested_start_time' => $data['requested_start_time'],
            'requested_end_time' => $data['requested_end_time'],
            'reason' => $data['reason'],
            'status' => ScheduleChangeRequest::STATUS_PENDING,
        ]);
    }

    protected function buildScheduleLabel(string $dayOfWeek, string $startTime, string $endTime, string $startDate, ?string $endDate): string
    {
        $preview = new Course([
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $preview->formattedSchedule();
    }
}
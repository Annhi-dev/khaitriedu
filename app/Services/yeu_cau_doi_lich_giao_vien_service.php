<?php

namespace App\Services;

use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\YeuCauDoiLich;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class TeacherScheduleChangeRequestService
{
    public function paginateRequests(NguoiDung $teacher, array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $search = trim((string) ($filters['search'] ?? ''));

        return YeuCauDoiLich::query()
            ->with([
                'course.subject.category',
                'classRoom.subject.category',
                'classRoom.room',
                'classSchedule.room',
                'requestedRoom',
                'reviewer',
            ])
            ->where('teacher_id', $teacher->id)
            ->when(in_array($status, YeuCauDoiLich::filterableStatuses(), true), fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('course', function (Builder $courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', '%' . $search . '%')
                            ->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'));
                    })->orWhereHas('classRoom', function (Builder $classQuery) use ($search) {
                        $classQuery->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', '%' . $search . '%'))
                            ->orWhere('id', 'like', '%' . $search . '%');
                    })->orWhere('reason', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("case when status = '" . YeuCauDoiLich::STATUS_PENDING . "' then 0 else 1 end")
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();
    }

    public function createRequest(KhoaHoc $course, NguoiDung $teacher, array $data): YeuCauDoiLich
    {
        if ((int) $course->teacher_id !== (int) $teacher->id) {
            throw ValidationException::withMessages([
                'course' => 'Bạn không có quyền gửi yêu cầu dời buổi cho lớp học này.',
            ]);
        }

        if (! in_array($course->status, KhoaHoc::schedulingStatuses(), true)) {
            throw ValidationException::withMessages([
                'course' => 'Chỉ lớp đã có lịch chính thức mới được gửi yêu cầu dời buổi.',
            ]);
        }

        foreach (['day_of_week', 'start_date', 'start_time', 'end_time'] as $attribute) {
            if (! $course->{$attribute}) {
                throw ValidationException::withMessages([
                'course' => 'Lớp học này chưa có đủ thông tin lịch hiện tại để tạo yêu cầu dời buổi.',
                ]);
            }
        }

        if (YeuCauDoiLich::query()->pending()->where('teacher_id', $teacher->id)->where('course_id', $course->id)->exists()) {
            throw ValidationException::withMessages([
                'course' => 'Lớp học này đang có một yêu cầu dời buổi chờ admin xử lý.',
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
                'requested_start_time' => 'Buổi dạy bù đề xuất đang trùng với lịch hiện tại của lớp học.',
            ]);
        }

        return YeuCauDoiLich::create([
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'current_schedule' => $currentSchedule,
            'requested_day_of_week' => $data['requested_day_of_week'],
            'requested_date' => $data['requested_date'],
            'requested_end_date' => $requestedEndDate,
            'requested_start_time' => $data['requested_start_time'],
            'requested_end_time' => $data['requested_end_time'],
            'reason' => $data['reason'],
            'status' => YeuCauDoiLich::STATUS_PENDING,
        ]);
    }

    public function createForClassSchedule(LichHoc $schedule, NguoiDung $teacher, array $data): YeuCauDoiLich
    {
        $schedule->loadMissing(['classRoom.subject.category', 'classRoom.room', 'classRoom.course', 'room']);

        if (! $schedule->classRoom || (int) $schedule->classRoom->teacher_id !== (int) $teacher->id) {
            throw ValidationException::withMessages([
                'schedule' => 'Bạn không có quyền gửi yêu cầu dời buổi cho buổi học này.',
            ]);
        }

        if (YeuCauDoiLich::query()
            ->pending()
            ->where('teacher_id', $teacher->id)
            ->where('class_schedule_id', $schedule->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'schedule' => 'Buổi học này đang có một yêu cầu dời buổi chờ admin xử lý.',
            ]);
        }

        $requestedStartAt = Carbon::parse($data['requested_start_at']);
        $requestedEndAt = Carbon::parse($data['requested_end_at']);
        $requestedDay = $requestedStartAt->englishDayOfWeek;
        $currentRoomId = $schedule->room_id ?: $schedule->classRoom?->room_id;
        $requestedRoomId = $data['requested_room_id'] ?? null;

        if ($requestedRoomId !== null && (int) $requestedRoomId === (int) $currentRoomId) {
            $requestedRoomId = null;
        }

        if ($requestedDay === $schedule->day_of_week
            && $requestedStartAt->format('H:i') === substr((string) $schedule->start_time, 0, 5)
            && $requestedEndAt->format('H:i') === substr((string) $schedule->end_time, 0, 5)
            && $requestedRoomId === null) {
            throw ValidationException::withMessages([
                'requested_start_at' => 'Buổi dạy bù đề xuất đang trùng với lịch hiện tại của buổi học.',
            ]);
        }

        return YeuCauDoiLich::create([
            'teacher_id' => $teacher->id,
            'course_id' => $schedule->classRoom->course_id,
            'class_room_id' => $schedule->classRoom->id,
            'class_schedule_id' => $schedule->id,
            'requested_room_id' => $requestedRoomId,
            'current_schedule' => $schedule->label(),
            'requested_day_of_week' => $requestedDay,
            'requested_date' => $requestedStartAt->toDateString(),
            'requested_end_date' => $requestedStartAt->toDateString(),
            'requested_start_time' => $requestedStartAt->format('H:i'),
            'requested_end_time' => $requestedEndAt->format('H:i'),
            'reason' => $data['reason'],
            'status' => YeuCauDoiLich::STATUS_PENDING,
        ]);
    }

    protected function buildScheduleLabel(string $dayOfWeek, string $startTime, string $endTime, string $startDate, ?string $endDate): string
    {
        $preview = new KhoaHoc([
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $preview->formattedSchedule();
    }
}

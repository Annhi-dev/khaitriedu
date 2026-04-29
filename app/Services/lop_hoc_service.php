<?php

namespace App\Services;

use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\KhoaHoc;
use App\Models\PhongHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminClassRoomService
{
    public function __construct(
        protected AdminScheduleConflictService $conflictService,
    ) {
    }

    public function create(array $data): LopHoc
    {
        $subject = MonHoc::query()->findOrFail((int) $data['subject_id']);
        $course = KhoaHoc::query()->findOrFail((int) $data['course_id']);
        $teacher = NguoiDung::query()
            ->teachers()
            ->where('status', NguoiDung::STATUS_ACTIVE)
            ->find((int) $data['teacher_id']);
        $room = PhongHoc::query()
            ->where('status', PhongHoc::STATUS_ACTIVE)
            ->find((int) $data['room_id']);

        if ((int) $course->subject_id !== (int) $subject->id) {
            throw ValidationException::withMessages([
                'course_id' => 'Khóa học được chọn không thuộc môn học này.',
            ]);
        }

        if (! $teacher) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Giảng viên không hợp lệ hoặc đang không hoạt động.',
            ]);
        }

        if (! $room) {
            throw ValidationException::withMessages([
                'room_id' => 'Phòng học không hợp lệ hoặc đang không hoạt động.',
            ]);
        }

        $startDate = $data['start_date'] ?? now()->toDateString();
        $endDate = Carbon::parse($startDate)
            ->addMonths(max(1, (int) ($subject->duration ?? 1)))
            ->toDateString();

        foreach ($data['schedules'] as $slot) {
            $days = [$slot['day']];
            $start = $slot['start'];
            $end = $slot['end'];

            if ($this->conflictService->teacherHasConflict($teacher->id, $days, $start, $end, (string) $startDate, (string) $endDate, $course->id)) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Giảng viên đã có lớp vào khung giờ ' . $slot['day'] . ' ' . $start . '-' . $end . '.',
                ]);
            }

            if ($this->conflictService->roomHasConflict($room->id, $days, $start, $end, (string) $startDate, (string) $endDate)) {
                throw ValidationException::withMessages([
                    'room_id' => 'Phòng học đã được sử dụng vào khung giờ ' . $slot['day'] . ' ' . $start . '-' . $end . '.',
                ]);
            }
        }

        return DB::transaction(function () use ($data, $subject, $course, $teacher, $room): LopHoc {
            $classRoom = LopHoc::create([
                'subject_id' => $subject->id,
                'course_id' => $course->id,
                'name' => $data['name'] ?? $course->title,
                'room_id' => $room->id,
                'teacher_id' => $teacher->id,
                'start_date' => $data['start_date'] ?? null,
                'duration' => $subject->duration,
                'note' => $data['note'] ?? null,
                'status' => LopHoc::STATUS_OPEN,
            ]);

            foreach ($data['schedules'] as $slot) {
                LichHoc::create([
                    'lop_hoc_id' => $classRoom->id,
                    'teacher_id' => $teacher->id,
                    'room_id' => $room->id,
                    'day_of_week' => $slot['day'],
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                ]);
            }

            return $classRoom;
        });
    }
}

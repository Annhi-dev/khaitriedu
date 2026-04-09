<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Collection;

class StudentScheduleService
{
    public function scheduleData(User $student): array
    {
        $enrollments = Enrollment::query()
            ->where('user_id', $student->id)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->whereNotNull('course_id')
            ->with([
                'course.subject',
                'assignedTeacher',
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules',
                'classRoom.enrollments' => fn ($query) => $query
                    ->whereIn('status', Enrollment::courseAccessStatuses())
                    ->with('user')
                    ->orderByDesc('id'),
            ])
            ->orderByDesc('id')
            ->get();

        $classRoomIds = $enrollments
            ->pluck('lop_hoc_id')
            ->filter()
            ->unique()
            ->values();

        $attendanceSummaries = collect();

        if ($classRoomIds->isNotEmpty()) {
            $attendanceRows = AttendanceRecord::query()
                ->where('student_id', $student->id)
                ->whereIn('class_room_id', $classRoomIds)
                ->orderByDesc('attendance_date')
                ->orderByDesc('id')
                ->get();

            $attendanceSummaries = $attendanceRows
                ->groupBy('class_room_id')
                ->map(function (Collection $rows) {
                    $total = $rows->count();
                    $present = $rows->where('status', AttendanceRecord::STATUS_PRESENT)->count();
                    $late = $rows->where('status', AttendanceRecord::STATUS_LATE)->count();
                    $excused = $rows->where('status', AttendanceRecord::STATUS_EXCUSED)->count();
                    $absent = $rows->where('status', AttendanceRecord::STATUS_ABSENT)->count();

                    return [
                        'total' => $total,
                        'present' => $present,
                        'late' => $late,
                        'excused' => $excused,
                        'absent' => $absent,
                        'present_rate' => $total > 0
                            ? (int) round((($present + $late + $excused) / $total) * 100)
                            : 0,
                        'recent' => $rows->take(5)->values(),
                    ];
                });
        }

        return [
            'enrollments' => $enrollments,
            'attendanceSummaries' => $attendanceSummaries,
        ];
    }
}


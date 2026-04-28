<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\User;
use Carbon\CarbonInterface;
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

        Enrollment::syncDisplayStatusesByClass($enrollments);

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
            'weeklyTimetable' => $this->weeklyTimetable($student),
        ];
    }

    public function weeklyEntries(User $student): Collection
    {
        $enrollments = Enrollment::query()
            ->where('user_id', $student->id)
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->whereNotNull('course_id')
            ->with([
                'course.subject.category',
                'assignedTeacher',
                'classRoom.room',
                'classRoom.teacher',
                'classRoom.schedules',
            ])
            ->orderByDesc('id')
            ->get();

        Enrollment::syncDisplayStatusesByClass($enrollments);

        return $enrollments
            ->flatMap(function (Enrollment $enrollment) {
                if ($enrollment->classRoom && $enrollment->classRoom->schedules->isNotEmpty()) {
                    $classRoom = $enrollment->classRoom;
                    $classStatus = (string) ($classRoom->status ?? '');
                    $classFinished = in_array($classStatus, [ClassRoom::STATUS_COMPLETED, ClassRoom::STATUS_CLOSED], true)
                        || $this->isClassRoomFinished($classRoom);

                    if ($classFinished) {
                        return collect();
                    }

                    return $classRoom->schedules->map(function (ClassSchedule $schedule) use ($enrollment, $classRoom, $classStatus, $classFinished) {
                        $roomName = $schedule->room?->name ?? $classRoom->room?->name ?? 'Chua phan phong';
                        $teacherName = $classRoom->teacher?->displayName() ?? $enrollment->assignedTeacher?->displayName() ?? 'Chua phan cong';
                        $status = $enrollment->displayStatus();

                        return [
                            'id' => 'student-' . $enrollment->id . '-' . $schedule->id,
                            'day_of_week' => $schedule->day_of_week,
                            'start_time' => substr((string) $schedule->start_time, 0, 5),
                            'end_time' => substr((string) $schedule->end_time, 0, 5),
                            'status' => $enrollment->displayStatus(),
                            'class_status' => $classStatus,
                            'class_finished' => $classFinished,
                            'title' => $classRoom->displayName(),
                            'subtitle' => $enrollment->course?->subject?->name ?? 'Lop da xep',
                            'meta' => implode(' • ', array_filter([$teacherName, $roomName])),
                            'badge' => $enrollment->displayStatusLabel(),
                            'badge_class' => match ($status) {
                                Enrollment::STATUS_ACTIVE, Enrollment::STATUS_ENROLLED, Enrollment::STATUS_SCHEDULED => 'bg-emerald-100 text-emerald-700',
                                Enrollment::STATUS_PENDING, Enrollment::STATUS_APPROVED => 'bg-cyan-100 text-cyan-700',
                                default => 'bg-slate-100 text-slate-600',
                            },
                            'tone' => 'emerald',
                            'url' => $enrollment->course ? route('courses.show', $enrollment->course->id) : null,
                            'primary_label' => 'Xem khoa hoc',
                        ];
                    });
                }

                if ($enrollment->course && $enrollment->course->isPendingOpen() && $enrollment->course->meetingDayValues() !== [] && $enrollment->course->start_time && $enrollment->course->end_time) {
                    $teacherName = $enrollment->course->teacher?->displayName() ?? $enrollment->assignedTeacher?->displayName() ?? 'Chua phan cong';
                    $roomName = $enrollment->classRoom?->room?->name ?? 'Đang chờ mở lớp';

                    return collect($enrollment->course->meetingDayValues())->map(function (string $dayOfWeek) use ($enrollment, $teacherName, $roomName) {
                        return [
                            'id' => 'pending-' . $enrollment->id . '-' . $dayOfWeek,
                            'day_of_week' => $dayOfWeek,
                            'start_time' => substr((string) $enrollment->course->start_time, 0, 5),
                            'end_time' => substr((string) $enrollment->course->end_time, 0, 5),
                            'status' => $enrollment->displayStatus(),
                            'title' => $enrollment->course->title ?? $enrollment->subject?->name ?? 'Lop cho mo',
                            'subtitle' => $enrollment->course->subject?->name ?? 'Yeu cau lich hoc',
                            'meta' => implode(' • ', array_filter([$teacherName, $roomName])),
                            'badge' => 'Đang chờ mở lớp',
                            'badge_class' => 'bg-amber-100 text-amber-700',
                            'tone' => 'amber',
                            'url' => route('courses.show', $enrollment->course->id),
                            'primary_label' => 'Xem khoa hoc',
                        ];
                    });
                }

                return collect();
            })
            ->values();
    }

    protected function isClassRoomFinished(ClassRoom $classRoom): bool
    {
        $endDate = $classRoom->scheduleRangeEnd();

        return $endDate !== null && now()->startOfDay()->gt($endDate->copy()->startOfDay());
    }

    public function weeklyTimetable(User $student, ?CarbonInterface $reference = null): array
    {
        return app(WeeklyTimetableService::class)->build($this->weeklyEntries($student), $reference);
    }
}

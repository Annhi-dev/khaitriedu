<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class TeacherScheduleService
{
    public function dashboardData(User $teacher): array
    {
        $classes = $this->assignedClasses($teacher);
        $weekSchedule = $this->weekSchedule($teacher);
        $today = CarbonImmutable::now();

        return [
            'classes' => $classes,
            'todaySchedule' => $weekSchedule
                ->filter(fn (array $item) => $item['starts_at']->isSameDay($today))
                ->values(),
            'weekSchedule' => $weekSchedule,
            'notifications' => Notification::query()
                ->where('user_id', $teacher->id)
                ->latest()
                ->take(5)
                ->get(),
            'requestUpdates' => ScheduleChangeRequest::query()
                ->with(['classRoom.subject.category', 'classSchedule', 'course.subject.category', 'reviewer'])
                ->where('teacher_id', $teacher->id)
                ->latest()
                ->take(5)
                ->get(),
            'quickLinks' => $classes->take(4)->values(),
        ];
    }

    public function assignedClasses(User $teacher): Collection
    {
        return ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->with(['subject.category', 'room', 'schedules'])
            ->withCount([
                'enrollments as students_count' => fn ($query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function weekSchedule(User $teacher, ?CarbonInterface $reference = null): Collection
    {
        $reference = $reference
            ? CarbonImmutable::instance($reference->toImmutable())
            : CarbonImmutable::now();
        $weekStart = $reference->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $reference->endOfWeek(CarbonInterface::SUNDAY);

        return $this->assignedClasses($teacher)
            ->flatMap(function (ClassRoom $classRoom) use ($weekStart, $weekEnd) {
                return $classRoom->schedules->map(function (ClassSchedule $schedule) use ($classRoom, $weekStart, $weekEnd) {
                    $occurrenceDate = $schedule->occurrenceForWeek($weekStart);

                    if (! $occurrenceDate || $occurrenceDate->gt($weekEnd)) {
                        return null;
                    }

                    return [
                        'class_room' => $classRoom,
                        'schedule' => $schedule,
                        'starts_at' => CarbonImmutable::parse(
                            $occurrenceDate->format('Y-m-d') . ' ' . substr((string) $schedule->start_time, 0, 8)
                        ),
                        'ends_at' => CarbonImmutable::parse(
                            $occurrenceDate->format('Y-m-d') . ' ' . substr((string) $schedule->end_time, 0, 8)
                        ),
                        'room_label' => $classRoom->room?->name ?? 'Chưa phân phòng',
                    ];
                });
            })
            ->filter()
            ->sortBy('starts_at')
            ->values();
    }
}

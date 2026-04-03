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
            ->with(['subject.category', 'room', 'course', 'schedules.room'])
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

        return $this->scheduleForRange($teacher, $weekStart, $weekEnd);
    }

    public function scheduleForRange(User $teacher, CarbonInterface $periodStart, CarbonInterface $periodEnd): Collection
    {
        $periodStartImmutable = CarbonImmutable::instance($periodStart->toImmutable())->startOfDay();
        $periodEndImmutable = CarbonImmutable::instance($periodEnd->toImmutable())->endOfDay();

        return $this->assignedClasses($teacher)
            ->flatMap(function (ClassRoom $classRoom) use ($periodStartImmutable, $periodEndImmutable) {
                return $classRoom->schedules->flatMap(function (ClassSchedule $schedule) use ($classRoom, $periodStartImmutable, $periodEndImmutable) {
                    return $this->scheduleOccurrencesWithin($schedule, $periodStartImmutable, $periodEndImmutable)
                        ->map(function (CarbonImmutable $occurrenceDate) use ($classRoom, $schedule) {
                            return [
                                'class_room' => $classRoom,
                                'schedule' => $schedule,
                                'starts_at' => CarbonImmutable::parse(
                                    $occurrenceDate->format('Y-m-d') . ' ' . substr((string) $schedule->start_time, 0, 8)
                                ),
                                'ends_at' => CarbonImmutable::parse(
                                    $occurrenceDate->format('Y-m-d') . ' ' . substr((string) $schedule->end_time, 0, 8)
                                ),
                                'room_label' => $schedule->room?->name ?? $classRoom->room?->name ?? 'Chua phan phong',
                            ];
                        });
                });
            })
            ->filter()
            ->sortBy('starts_at')
            ->values();
    }

    protected function scheduleOccurrencesWithin(
        ClassSchedule $schedule,
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd
    ): Collection {
        $dayIndex = array_search($schedule->day_of_week, array_keys(ClassSchedule::$dayOptions), true);

        if ($dayIndex === false) {
            return collect();
        }

        $candidate = $periodStart->startOfWeek(CarbonInterface::MONDAY)->addDays($dayIndex);

        while ($candidate->lt($periodStart)) {
            $candidate = $candidate->addWeek();
        }

        if ($schedule->classRoom?->start_date) {
            $classStartDate = $schedule->classRoom->start_date->toImmutable()->startOfDay();

            while ($candidate->lt($classStartDate)) {
                $candidate = $candidate->addWeek();
            }
        }

        $courseEndDate = $schedule->classRoom?->course?->end_date
            ? $schedule->classRoom->course->end_date->toImmutable()->endOfDay()
            : null;

        $occurrences = collect();

        while ($candidate->lte($periodEnd) && ($courseEndDate === null || $candidate->lte($courseEndDate))) {
            $occurrences->push($candidate);
            $candidate = $candidate->addWeek();
        }

        return $occurrences;
    }
}

<?php

namespace App\Services;

use App\Models\LopHoc;
use App\Models\LichHoc;
use App\Models\GhiDanh;
use App\Models\ThongBao;
use App\Models\YeuCauDoiLich;
use App\Models\NguoiDung;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class TeacherScheduleService
{
    public function dashboardData(NguoiDung $teacher): array
    {
        $classes = $this->assignedClasses($teacher);
        $weekSchedule = $this->weekSchedule($teacher);
        $today = CarbonImmutable::now();

        return [
            'classes' => $classes,
            'weeklyTimetable' => $this->weeklyTimetable($teacher, $today),
            'todaySchedule' => $weekSchedule
                ->filter(fn (array $item) => $item['starts_at']->isSameDay($today))
                ->values(),
            'weekSchedule' => $weekSchedule,
            'notifications' => ThongBao::query()
                ->where('user_id', $teacher->id)
                ->latest()
                ->take(5)
                ->get(),
            'requestUpdates' => YeuCauDoiLich::query()
                ->with(['classRoom.subject.category', 'classSchedule', 'course.subject.category', 'reviewer'])
                ->where('teacher_id', $teacher->id)
                ->latest()
                ->take(5)
                ->get(),
            'quickLinks' => $classes->take(4)->values(),
        ];
    }

    public function assignedClasses(NguoiDung $teacher): Collection
    {
        return LopHoc::query()
            ->where('teacher_id', $teacher->id)
            ->with(['subject.category', 'room', 'course', 'schedules.room'])
            ->withCount([
                'enrollments as students_count' => fn ($query) => $query->whereIn('status', GhiDanh::courseAccessStatuses()),
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function weeklyEntries(NguoiDung $teacher): Collection
    {
        return $this->assignedClasses($teacher)
            ->flatMap(function (LopHoc $classRoom) {
                return $classRoom->schedules->map(function (LichHoc $schedule) use ($classRoom) {
                    $roomName = $schedule->room?->name ?? $classRoom->room?->name ?? 'Chua phan phong';
                    $studentsCount = (int) ($classRoom->students_count ?? 0);

                    return [
                        'id' => 'teacher-' . $classRoom->id . '-' . $schedule->id,
                        'day_of_week' => $schedule->day_of_week,
                        'start_time' => substr((string) $schedule->start_time, 0, 5),
                        'end_time' => substr((string) $schedule->end_time, 0, 5),
                        'title' => $classRoom->displayName(),
                        'subtitle' => $classRoom->subject?->name ?? 'Chua co mon hoc',
                        'meta' => implode(' • ', array_filter([$roomName, $studentsCount . ' hoc vien'])),
                        'badge' => $classRoom->subject?->category?->name ?? 'Lich day',
                        'badge_class' => 'bg-cyan-100 text-cyan-700',
                        'tone' => 'cyan',
                        'url' => route('teacher.classes.show', $classRoom),
                        'primary_label' => 'Mo lop',
                        'secondary_url' => $classRoom->course ? route('teacher.schedule-change-requests.create', $classRoom->course) : null,
                        'secondary_label' => 'Dời buổi',
                    ];
                });
            })
            ->values();
    }

    public function weeklyTimetable(NguoiDung $teacher, ?CarbonInterface $reference = null): array
    {
        return app(WeeklyTimetableService::class)->build($this->weeklyEntries($teacher), $reference);
    }

    public function weekSchedule(NguoiDung $teacher, ?CarbonInterface $reference = null): Collection
    {
        $reference = $reference
            ? CarbonImmutable::instance($reference->toImmutable())
            : CarbonImmutable::now();
        $weekStart = $reference->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $reference->endOfWeek(CarbonInterface::SUNDAY);

        return $this->scheduleForRange($teacher, $weekStart, $weekEnd);
    }

    public function scheduleForRange(NguoiDung $teacher, CarbonInterface $periodStart, CarbonInterface $periodEnd): Collection
    {
        $periodStartImmutable = CarbonImmutable::instance($periodStart->toImmutable())->startOfDay();
        $periodEndImmutable = CarbonImmutable::instance($periodEnd->toImmutable())->endOfDay();

        return $this->assignedClasses($teacher)
            ->flatMap(function (LopHoc $classRoom) use ($periodStartImmutable, $periodEndImmutable) {
                return $classRoom->schedules->flatMap(function (LichHoc $schedule) use ($classRoom, $periodStartImmutable, $periodEndImmutable) {
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
        LichHoc $schedule,
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd
    ): Collection {
        $dayIndex = array_search($schedule->day_of_week, array_keys(LichHoc::$dayOptions), true);

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

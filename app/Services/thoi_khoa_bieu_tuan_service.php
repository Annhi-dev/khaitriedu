<?php

namespace App\Services;

use App\Helpers\ScheduleHelper;
use App\Models\ClassSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class WeeklyTimetableService
{
    public function build(Collection|array $entries, ?CarbonInterface $reference = null): array
    {
        $entries = collect($entries)
            ->filter(fn ($entry) => $this->hasRequiredFields($entry))
            ->map(fn (array $entry, int $index) => $this->normalizeEntry($entry, $index))
            ->values();

        $reference = $reference
            ? CarbonImmutable::instance($reference->toImmutable())
            : CarbonImmutable::now();

        $weekStart = $reference->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $reference->endOfWeek(CarbonInterface::SUNDAY);
        $days = $this->buildDays($weekStart);
        $dayKeys = array_keys(ClassSchedule::$dayOptions);
        $slots = $this->buildSlots($entries);

        if ($slots->isEmpty()) {
            $slots = collect(ScheduleHelper::defaultTimetableSlots())
                ->map(fn (array $slot) => [
                    'key' => $this->slotKey($slot['start_time'], $slot['end_time']),
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'label' => $slot['label'],
                ]);
        }

        $matrix = [];

        foreach ($slots as $slot) {
            $matrix[$slot['key']] = array_fill_keys($dayKeys, []);
        }

        foreach ($entries as $entry) {
            $slotKey = $this->slotKey($entry['start_time'], $entry['end_time']);

            if (! isset($matrix[$slotKey])) {
                $matrix[$slotKey] = array_fill_keys($dayKeys, []);
            }

            if (! array_key_exists($entry['day_of_week'], $matrix[$slotKey])) {
                continue;
            }

            $matrix[$slotKey][$entry['day_of_week']][] = $entry;
        }

        $slots = $slots
            ->sortBy(fn (array $slot) => $this->minutesSinceMidnight($slot['start_time']) * 100 + $this->minutesSinceMidnight($slot['end_time']))
            ->values();

        $sortedMatrix = [];

        foreach ($slots as $slot) {
            $sortedMatrix[$slot['key']] = $matrix[$slot['key']] ?? array_fill_keys($dayKeys, []);
        }

        return [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekLabel' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m/Y'),
            'entries' => $entries->all(),
            'days' => $days,
            'slots' => $slots->all(),
            'matrix' => $sortedMatrix,
            'totalEntries' => $entries->count(),
        ];
    }

    protected function hasRequiredFields(array $entry): bool
    {
        return ! empty($entry['day_of_week'])
            && ! empty($entry['start_time'])
            && ! empty($entry['end_time']);
    }

    protected function normalizeEntry(array $entry, int $index): array
    {
        $startTime = substr((string) $entry['start_time'], 0, 5);
        $endTime = substr((string) $entry['end_time'], 0, 5);

        return [
            'id' => $entry['id'] ?? 'slot-' . $index,
            'day_of_week' => (string) $entry['day_of_week'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'title' => (string) ($entry['title'] ?? 'Buổi học'),
            'subtitle' => (string) ($entry['subtitle'] ?? ''),
            'meta' => (string) ($entry['meta'] ?? ''),
            'badge' => (string) ($entry['badge'] ?? ''),
            'badge_class' => (string) ($entry['badge_class'] ?? 'bg-slate-100 text-slate-600'),
            'tone' => (string) ($entry['tone'] ?? 'slate'),
            'url' => $entry['url'] ?? null,
            'primary_label' => (string) ($entry['primary_label'] ?? 'Xem chi tiết'),
            'secondary_url' => $entry['secondary_url'] ?? null,
            'secondary_label' => (string) ($entry['secondary_label'] ?? ''),
            'description' => (string) ($entry['description'] ?? ''),
            'time_label' => ScheduleHelper::timeRangeLabel($startTime, $endTime),
        ];
    }

    protected function buildDays(CarbonImmutable $weekStart): array
    {
        $days = [];

        foreach (array_keys(ClassSchedule::$dayOptions) as $index => $dayKey) {
            $date = $weekStart->addDays($index);

            $days[] = [
                'key' => $dayKey,
                'label' => ClassSchedule::$dayOptions[$dayKey] ?? $dayKey,
                'date' => $date,
                'date_label' => $date->format('d/m'),
                'long_label' => $date->format('d/m/Y'),
                'is_today' => $date->isSameDay(CarbonImmutable::now()),
            ];
        }

        return $days;
    }

    protected function buildSlots(Collection $entries): Collection
    {
        return $entries
            ->map(fn (array $entry) => [
                'key' => $this->slotKey($entry['start_time'], $entry['end_time']),
                'start_time' => $entry['start_time'],
                'end_time' => $entry['end_time'],
                'label' => ScheduleHelper::timeRangeLabel($entry['start_time'], $entry['end_time']),
            ])
            ->unique('key')
            ->values();
    }

    protected function slotKey(string $startTime, string $endTime): string
    {
        return substr($startTime, 0, 5) . '|' . substr($endTime, 0, 5);
    }

    protected function minutesSinceMidnight(string $time): int
    {
        [$hour, $minute] = array_pad(array_map('intval', explode(':', substr($time, 0, 5))), 2, 0);

        return $hour * 60 + $minute;
    }
}

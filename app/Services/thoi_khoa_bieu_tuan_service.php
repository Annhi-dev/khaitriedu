<?php

namespace App\Services;

use App\Helpers\ScheduleHelper;
use App\Models\LichHoc;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class WeeklyTimetableService
{
    protected const NON_CONFLICTING_STATUSES = [
        'completed',
        'closed',
    ];

    public function build(Collection|array $entries, ?CarbonInterface $reference = null): array
    {
        $entries = collect($entries)
            ->filter(fn ($entry) => $this->hasRequiredFields($entry))
            ->map(fn (array $entry, int $index) => $this->normalizeEntry($entry, $index))
            ->values();

        [$entries, $conflicts] = $this->annotateConflicts($entries);

        $reference = $reference
            ? CarbonImmutable::instance($reference->toImmutable())
            : CarbonImmutable::now();

        $weekStart = $reference->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $reference->endOfWeek(CarbonInterface::SUNDAY);
        $days = $this->buildDays($weekStart);
        $dayKeys = array_keys(LichHoc::$dayOptions);
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
            'hasConflicts' => $conflicts->isNotEmpty(),
            'conflictCount' => $conflicts->count(),
            'conflicts' => $conflicts->all(),
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
            'day_label' => (string) ($entry['day_label'] ?? (LichHoc::$dayOptions[$entry['day_of_week']] ?? $entry['day_of_week'])),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => (string) ($entry['status'] ?? ''),
            'class_status' => (string) ($entry['class_status'] ?? ''),
            'class_finished' => (bool) ($entry['class_finished'] ?? false),
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

        foreach (array_keys(LichHoc::$dayOptions) as $index => $dayKey) {
            $date = $weekStart->addDays($index);

            $days[] = [
                'key' => $dayKey,
                'label' => LichHoc::$dayOptions[$dayKey] ?? $dayKey,
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

    protected function annotateConflicts(Collection $entries): array
    {
        $entries = $entries
            ->map(function (array $entry) {
                $entry['conflict'] = false;
                $entry['conflict_notes'] = [];
                $entry['conflict_note'] = null;
                $entry['conflict_count'] = 0;
                return $entry;
            })
            ->values()
            ->all();

        $conflicts = collect();
        $count = count($entries);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $first = $entries[$i];
                $second = $entries[$j];

                if (($first['day_of_week'] ?? null) !== ($second['day_of_week'] ?? null)) {
                    continue;
                }

                if (! $this->rangesOverlap($first['start_time'], $first['end_time'], $second['start_time'], $second['end_time'])) {
                    continue;
                }

                if (! $this->canConflicts($first, $second)) {
                    continue;
                }

                $firstNote = $this->buildConflictNote($first, $second);
                $secondNote = $this->buildConflictNote($second, $first);

                $entries[$i]['conflict'] = true;
                $entries[$i]['conflict_notes'][] = $firstNote;
                $entries[$i]['conflict_count'] = count($entries[$i]['conflict_notes']);
                $entries[$i]['conflict_note'] = $entries[$i]['conflict_notes'][0];

                $entries[$j]['conflict'] = true;
                $entries[$j]['conflict_notes'][] = $secondNote;
                $entries[$j]['conflict_count'] = count($entries[$j]['conflict_notes']);
                $entries[$j]['conflict_note'] = $entries[$j]['conflict_notes'][0];

                $conflicts->push([
                    'first_id' => $first['id'],
                    'first_status' => $first['status'] ?? null,
                    'second_id' => $second['id'],
                    'second_status' => $second['status'] ?? null,
                    'day_of_week' => $first['day_of_week'],
                    'day_label' => $first['day_label'] ?? $first['day_of_week'],
                    'first_title' => $first['title'] ?? 'Buổi học',
                    'second_title' => $second['title'] ?? 'Buổi học',
                    'time_label' => $first['time_label'] ?? ($first['start_time'] . ' - ' . $first['end_time']),
                ]);
            }
        }

        $entries = collect($entries)->map(function (array $entry) {
            $entry['conflict_notes'] = array_values(array_unique($entry['conflict_notes']));
            $entry['conflict_count'] = count($entry['conflict_notes']);
            $entry['conflict_note'] = $entry['conflict_notes'][0] ?? null;

            return $entry;
        });

        return [$entries, $conflicts];
    }

    protected function buildConflictNote(array $entry, array $other): string
    {
        $otherTitle = $other['title'] ?? 'buổi học khác';
        $otherDayLabel = $other['day_label'] ?? ($other['day_of_week'] ?? 'chưa rõ ngày');
        $otherTimeLabel = $other['time_label'] ?? (($other['start_time'] ?? '') . ' - ' . ($other['end_time'] ?? ''));

        return sprintf('Trùng với %s, %s, %s', $otherTitle, $otherDayLabel, $otherTimeLabel);
    }

    protected function canConflicts(array $first, array $second): bool
    {
        return ! $this->isNonConflictingEntry($first)
            && ! $this->isNonConflictingEntry($second);
    }

    protected function isNonConflictingEntry(array $entry): bool
    {
        $status = (string) ($entry['status'] ?? '');
        $classStatus = (string) ($entry['class_status'] ?? '');

        return (bool) ($entry['class_finished'] ?? false)
            || in_array($status, self::NON_CONFLICTING_STATUSES, true)
            || in_array($classStatus, self::NON_CONFLICTING_STATUSES, true);
    }

    protected function rangesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $startA = substr($startA, 0, 5);
        $endA = substr($endA, 0, 5);
        $startB = substr($startB, 0, 5);
        $endB = substr($endB, 0, 5);

        return $startA < $endB && $endA > $startB;
    }
}

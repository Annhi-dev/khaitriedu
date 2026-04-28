<?php

namespace App\Helpers;

use Carbon\Carbon;

class ScheduleHelper
{
    public static function normalizeTimeValue(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }

        $time = trim($time);

        if ($time === '') {
            return null;
        }

        foreach (['H:i', 'H:i:s', 'g:i A', 'g:i a', 'h:i A', 'h:i a'] as $format) {
            try {
                return Carbon::createFromFormat($format, $time)->format('H:i');
            } catch (\Throwable $exception) {
            }
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable $exception) {
            return $time;
        }
    }

    public static function periodMinutes(): int
    {
        return (int) config('schedule.period_minutes', 45);
    }

    public static function periodsPerSession(): int
    {
        return (int) config('schedule.periods_per_session', 3);
    }

    public static function sessionMinutes(): int
    {
        return self::periodMinutes() * self::periodsPerSession();
    }

    public static function sessionLabel(): string
    {
        return self::periodsPerSession() . ' tiết x ' . self::periodMinutes() . ' phút';
    }

    public static function timeRangeLabel(string $startTime, string $endTime): string
    {
        return substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5);
    }

    public static function defaultTimetableSlots(): array
    {
        $slotStarts = ['08:00', '13:30', '18:00', '19:00'];
        $slots = [];

        foreach ($slotStarts as $startTime) {
            $endTime = self::normalizeEndTime($startTime);

            $slots[] = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'label' => self::timeRangeLabel($startTime, $endTime),
            ];
        }

        return $slots;
    }

    public static function normalizeEndTime(string $startTime): string
    {
        $normalizedStartTime = self::normalizeTimeValue($startTime);

        if (! $normalizedStartTime) {
            return $startTime;
        }

        try {
            return Carbon::createFromFormat('H:i', $normalizedStartTime)
                ->addMinutes(self::sessionMinutes())
                ->format('H:i');
        } catch (\Throwable $exception) {
            return $normalizedStartTime;
        }
    }

    public static function normalizeTimeRange(?string $startTime, ?string $endTime = null): array
    {
        if (! $startTime) {
            return [
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        }

        return [
            'start_time' => $startTime,
            'end_time' => self::normalizeEndTime($startTime),
        ];
    }
}

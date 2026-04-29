<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class LichHoc extends Model
{
    protected $table = 'lich_hoc';

    protected $fillable = [
        'lop_hoc_id',
        'teacher_id',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public static array $dayOptions = [
        'Monday'    => 'Thứ 2',
        'Tuesday'   => 'Thứ 3',
        'Wednesday' => 'Thứ 4',
        'Thursday'  => 'Thứ 5',
        'Friday'    => 'Thứ 6',
        'Saturday'  => 'Thứ 7',
        'Sunday'    => 'Chủ nhật',
    ];

    public function classRoom()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function teacher()
    {
        return $this->belongsTo(NguoiDung::class, 'teacher_id');
    }

    public function room()
    {
        return $this->belongsTo(PhongHoc::class, 'room_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(DiemDanh::class, 'class_schedule_id');
    }

    public function scheduleChangeRequests()
    {
        return $this->hasMany(YeuCauDoiLich::class, 'class_schedule_id');
    }

    public function dayLabel(): string
    {
        return self::$dayOptions[$this->day_of_week] ?? $this->day_of_week;
    }

    public function timeRangeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5) . ' - ' . substr((string) $this->end_time, 0, 5);
    }

    public function label(): string
    {
        return $this->dayLabel() . ' | ' . $this->timeRangeLabel();
    }

    public function occurrenceForWeek(CarbonInterface $weekStart): ?CarbonImmutable
    {
        $dayIndex = array_search($this->day_of_week, array_keys(self::$dayOptions), true);

        if ($dayIndex === false) {
            return null;
        }

        $base = CarbonImmutable::instance($weekStart->toImmutable())->startOfDay();
        $candidate = $base->addDays($dayIndex);

        if ($this->classRoom?->start_date && $candidate->lt($this->classRoom->start_date->toImmutable()->startOfDay())) {
            return null;
        }

        return $candidate;
    }

    public function nextOccurrence(?CarbonInterface $reference = null): ?CarbonImmutable
    {
        $reference = $reference
            ? CarbonImmutable::instance($reference->toImmutable())
            : CarbonImmutable::now();

        $dayIndex = array_search($this->day_of_week, array_keys(self::$dayOptions), true);

        if ($dayIndex === false) {
            return null;
        }

        $candidate = $reference->startOfWeek(CarbonInterface::MONDAY)->addDays($dayIndex);

        if ($candidate->lt($reference->startOfDay())
            || ($candidate->isSameDay($reference) && substr((string) $this->end_time, 0, 5) <= $reference->format('H:i'))) {
            $candidate = $candidate->addWeek();
        }

        if ($this->classRoom?->start_date) {
            while ($candidate->lt($this->classRoom->start_date->toImmutable()->startOfDay())) {
                $candidate = $candidate->addWeek();
            }
        }

        return $candidate;
    }
}

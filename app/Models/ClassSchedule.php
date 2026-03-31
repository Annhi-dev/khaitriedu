<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    protected $table = 'lich_hoc';

    protected $fillable = [
        'lop_hoc_id',
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
        return $this->belongsTo(ClassRoom::class, 'lop_hoc_id');
    }

    public function dayLabel(): string
    {
        return self::$dayOptions[$this->day_of_week] ?? $this->day_of_week;
    }
}

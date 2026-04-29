<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiemDanh extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_EXCUSED = 'excused';

    protected $fillable = [
        'course_id',
        'class_room_id',
        'class_schedule_id',
        'enrollment_id',
        'student_id',
        'teacher_id',
        'attendance_date',
        'status',
        'note',
        'recorded_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'recorded_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PRESENT => 'Có mặt',
            self::STATUS_ABSENT => 'Vắng không phép',
            self::STATUS_LATE => 'Đi trễ',
            self::STATUS_EXCUSED => 'Có phép',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }

    public function classRoom()
    {
        return $this->belongsTo(LopHoc::class, 'class_room_id');
    }

    public function classSchedule()
    {
        return $this->belongsTo(LichHoc::class, 'class_schedule_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(GhiDanh::class, 'enrollment_id');
    }

    public function student()
    {
        return $this->belongsTo(NguoiDung::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(NguoiDung::class, 'teacher_id');
    }
}

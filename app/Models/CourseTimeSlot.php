<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTimeSlot extends Model
{
    use HasFactory;

    public const STATUS_PENDING_OPEN = 'pending_open';
    public const STATUS_OPEN_FOR_REGISTRATION = 'open_for_registration';
    public const STATUS_READY_TO_OPEN_CLASS = 'ready_to_open_class';
    public const STATUS_CLASS_OPENED = 'class_opened';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'room_id',
        'day_of_week',
        'slot_date',
        'start_time',
        'end_time',
        'registration_open_at',
        'registration_close_at',
        'min_students',
        'max_students',
        'status',
        'note',
    ];

    protected $casts = [
        'slot_date' => 'date',
        'registration_open_at' => 'datetime',
        'registration_close_at' => 'datetime',
        'min_students' => 'integer',
        'max_students' => 'integer',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING_OPEN => 'Chờ mở',
            self::STATUS_OPEN_FOR_REGISTRATION => 'Đang mở đăng ký',
            self::STATUS_READY_TO_OPEN_CLASS => 'Đủ điều kiện mở lớp',
            self::STATUS_CLASS_OPENED => 'Đã mở lớp',
            self::STATUS_CANCELLED => 'Đã hủy',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function formattedWindow(): string
    {
        $segments = [];

        if ($this->day_of_week) {
            $segments[] = match ($this->day_of_week) {
                'Monday' => 'Thứ 2',
                'Tuesday' => 'Thứ 3',
                'Wednesday' => 'Thứ 4',
                'Thursday' => 'Thứ 5',
                'Friday' => 'Thứ 6',
                'Saturday' => 'Thứ 7',
                'Sunday' => 'Chủ nhật',
                default => $this->day_of_week,
            };
        } elseif ($this->slot_date) {
            $segments[] = $this->slot_date->format('d/m/Y');
        }

        if ($this->start_time && $this->end_time) {
            $segments[] = $this->start_time . ' - ' . $this->end_time;
        }

        return $segments ? implode(' | ', $segments) : 'Chưa cấu hình khung giờ';
    }

    public function scopeOpenForRegistration($query)
    {
        return $query->where('status', self::STATUS_OPEN_FOR_REGISTRATION);
    }

    public function scopeReadyToOpenClass($query)
    {
        return $query->where('status', self::STATUS_READY_TO_OPEN_CLASS);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function choices()
    {
        return $this->hasMany(SlotRegistrationChoice::class);
    }

    public function registrations()
    {
        return $this->belongsToMany(
            SlotRegistration::class,
            'slot_registration_choices',
            'course_time_slot_id',
            'slot_registration_id'
        )->withPivot('priority')->withTimestamps();
    }
}

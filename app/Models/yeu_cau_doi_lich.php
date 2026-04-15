<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleChangeRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'teacher_id',
        'course_id',
        'class_room_id',
        'class_schedule_id',
        'requested_room_id',
        'current_schedule',
        'requested_day_of_week',
        'requested_date',
        'requested_end_date',
        'requested_start_time',
        'requested_end_time',
        'reason',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'requested_room_id' => 'integer',
        'requested_date' => 'date',
        'requested_end_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public static function filterableStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ duyệt',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            default => ucfirst((string) $this->status),
        };
    }

    public function currentScheduleLabel(): string
    {
        return $this->current_schedule ?: ($this->course?->formattedSchedule() ?? 'Chưa có lịch hiện tại');
    }

    public function requestedScheduleLabel(): string
    {
        $preview = new Course([
            'day_of_week' => $this->requested_day_of_week,
            'start_date' => optional($this->requested_date)->format('Y-m-d'),
            'end_date' => optional($this->requested_end_date)->format('Y-m-d'),
            'start_time' => $this->requested_start_time,
            'end_time' => $this->requested_end_time,
        ]);

        return $preview->formattedSchedule();
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

    public function requestedRoom()
    {
        return $this->belongsTo(Room::class, 'requested_room_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isClassScheduleRequest(): bool
    {
        return $this->class_schedule_id !== null;
    }

    public function targetTitle(): string
    {
        if ($this->classRoom) {
            return $this->classRoom->displayName();
        }

        return $this->course?->title ?? 'Lớp học đã bị xóa';
    }

    public function subjectName(): string
    {
        return $this->classRoom?->subject?->name
            ?? $this->course?->subject?->name
            ?? 'Chưa xác định';
    }

    public function categoryName(): string
    {
        return $this->classRoom?->subject?->category?->name
            ?? $this->course?->subject?->category?->name
            ?? 'Chưa phân nhóm';
    }

    public function currentRoomLabel(): string
    {
        return $this->classSchedule?->room?->name
            ?? $this->classRoom?->room?->name
            ?? 'Chưa phân phòng';
    }

    public function requestedRoomLabel(): string
    {
        return $this->requestedRoom?->name ?: $this->currentRoomLabel();
    }
}

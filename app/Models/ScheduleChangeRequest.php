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

    protected $table = 'schedule_change_requests';

    protected $fillable = [
        'teacher_id',
        'course_id',
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

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
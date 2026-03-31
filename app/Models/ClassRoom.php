<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;

    public const STATUS_OPEN      = 'open';
    public const STATUS_FULL      = 'full';
    public const STATUS_CLOSED    = 'closed';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'lop_hoc';

    protected $fillable = [
        'subject_id',
        'room_id',
        'teacher_id',
        'start_date',
        'duration',
        'status',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'duration'   => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'lop_hoc_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'lop_hoc_id');
    }

    public function students()
    {
        return $this->hasManyThrough(
            User::class,
            Enrollment::class,
            'lop_hoc_id',
            'id',
            'id',
            'user_id'
        );
    }

    // ─── Computed ───────────────────────────────────────────────────────────────

    public function enrolledCount(): int
    {
        return $this->enrollments()->count();
    }

    public function isFull(): bool
    {
        if (! $this->room) {
            return false;
        }

        return $this->enrolledCount() >= $this->room->capacity;
    }

    public function availableSlots(): int
    {
        if (! $this->room) {
            return 999;
        }

        return max(0, $this->room->capacity - $this->enrolledCount());
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN      => 'Đang mở',
            self::STATUS_FULL      => 'Đã đủ chỗ',
            self::STATUS_CLOSED    => 'Đã đóng',
            self::STATUS_COMPLETED => 'Hoàn thành',
            default                => ucfirst((string) $this->status),
        };
    }

    // ─── Conflict Checks ────────────────────────────────────────────────────────

    /**
     * Kiểm tra giáo viên có bị trùng lịch với lớp khác không.
     */
    public static function teacherHasConflict(
        int $teacherId,
        array $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): bool {
        return static::where('teacher_id', $teacherId)
            ->whereNot(fn ($q) => $q->where('status', self::STATUS_COMPLETED)->orWhere('status', self::STATUS_CLOSED))
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereHas('schedules', function ($q) use ($dayOfWeek, $startTime, $endTime) {
                $q->whereIn('day_of_week', $dayOfWeek)
                  ->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->exists();
    }

    /**
     * Kiểm tra phòng có bị trùng lịch không.
     */
    public static function roomHasConflict(
        int $roomId,
        array $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): bool {
        return static::where('room_id', $roomId)
            ->whereNot(fn ($q) => $q->where('status', self::STATUS_COMPLETED)->orWhere('status', self::STATUS_CLOSED))
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereHas('schedules', function ($q) use ($dayOfWeek, $startTime, $endTime) {
                $q->whereIn('day_of_week', $dayOfWeek)
                  ->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->exists();
    }
}

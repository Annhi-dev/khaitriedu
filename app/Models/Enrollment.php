<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const LEGACY_STATUS_CONFIRMED = 'confirmed';

    protected $table = 'dang_ky';

    protected $fillable = [
        'user_id',
        'subject_id',
        'course_id',
        'preferred_schedule',
        'assigned_teacher_id',
        'status',
        'note',
        'schedule',
        'start_time',
        'end_time',
        'preferred_days',
        'is_submitted',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_submitted' => 'boolean',
    ];

    public static function filterableStatuses(): array
    {
        return array_keys(self::statusOptions());
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Chờ duyệt',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            self::STATUS_SCHEDULED => 'Đã xếp lớp',
            self::STATUS_ACTIVE => 'Đang học',
            self::STATUS_COMPLETED => 'Hoàn thành',
        ];
    }

    public static function courseAccessStatuses(): array
    {
        return [
            self::LEGACY_STATUS_CONFIRMED,
            self::STATUS_SCHEDULED,
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
        ];
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('is_submitted', true);
    }

    public function normalizedStatus(): string
    {
        return $this->status === self::LEGACY_STATUS_CONFIRMED
            ? self::STATUS_SCHEDULED
            : (string) $this->status;
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->normalizedStatus()] ?? ucfirst((string) $this->status);
    }

    public function hasCourseAccess(): bool
    {
        return in_array($this->status, self::courseAccessStatuses(), true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
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
    public const STATUS_ENROLLED = 'enrolled';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const LEGACY_STATUS_CONFIRMED = 'confirmed';
    public const REQUEST_SOURCE_CUSTOM_SCHEDULE = 'custom_schedule';
    public const REQUEST_SOURCE_FIXED_CLASS = 'fixed_class';

    protected $table = 'dang_ky';

    protected $fillable = [
        'user_id',
        'subject_id',
        'course_id',
        'lop_hoc_id',
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
        'preferred_days' => 'array',
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
            self::STATUS_ENROLLED => 'Đã ghi danh',
            self::STATUS_SCHEDULED => 'Đã xếp lớp',
            self::STATUS_ACTIVE => 'Đang học',
            self::STATUS_COMPLETED => 'Hoàn thành',
        ];
    }

    public static function courseAccessStatuses(): array
    {
        return [
            self::LEGACY_STATUS_CONFIRMED,
            self::STATUS_ENROLLED,
            self::STATUS_SCHEDULED,
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
        ];
    }

    public static function requestSourceOptions(): array
    {
        return [
            self::REQUEST_SOURCE_CUSTOM_SCHEDULE => 'Yêu cầu lịch học riêng',
            self::REQUEST_SOURCE_FIXED_CLASS => 'Ghi danh lớp cố định',
        ];
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('is_submitted', true);
    }

    public function scopeForUserSubject(Builder $query, int $userId, int $subjectId): Builder
    {
        return $query->where('user_id', $userId)
            ->where(function (Builder $builder) use ($subjectId) {
                $builder->where('subject_id', $subjectId)
                    ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->where('subject_id', $subjectId));
            });
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

    public function requestSourceKey(): string
    {
        return $this->isFixedClassEnrollment()
            ? self::REQUEST_SOURCE_FIXED_CLASS
            : self::REQUEST_SOURCE_CUSTOM_SCHEDULE;
    }

    public function requestSourceLabel(): string
    {
        return self::requestSourceOptions()[$this->requestSourceKey()] ?? 'Chưa phân loại';
    }

    public function requestSourceBadgeType(): string
    {
        return $this->isFixedClassEnrollment() ? 'success' : 'info';
    }

    public function isFixedClassEnrollment(): bool
    {
        return $this->lop_hoc_id !== null
            || $this->normalizedStatus() === self::STATUS_ENROLLED;
    }

    public function isCustomScheduleRequest(): bool
    {
        return ! $this->isFixedClassEnrollment();
    }

    public function hasCourseAccess(): bool
    {
        return in_array($this->status, self::courseAccessStatuses(), true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'lop_hoc_id');
    }

    public function currentClassRoom(): ?ClassRoom
    {
        if ($this->classRoom) {
            return $this->classRoom;
        }

        return $this->course?->currentClassRoom();
    }

    public function currentClassRoomLabel(): string
    {
        return $this->currentClassRoom()?->displayName()
            ?? $this->course?->title
            ?? 'Chưa xếp lớp';
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}

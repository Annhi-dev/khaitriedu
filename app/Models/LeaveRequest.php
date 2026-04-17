<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    protected $table = 'yeu_cau_xin_phep';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'enrollment_id',
        'course_id',
        'class_room_id',
        'class_schedule_id',
        'attendance_date',
        'reason',
        'note',
        'status',
        'teacher_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_ACCEPTED => 'Đã chấp nhận',
            self::STATUS_REJECTED => 'Đã từ chối',
            self::STATUS_ACKNOWLEDGED => 'Đã ghi nhận',
        ];
    }

    public static function teacherReviewStatuses(): array
    {
        return [
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_ACKNOWLEDGED,
        ];
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_REJECTED, self::STATUS_ACKNOWLEDGED], true);
    }

    public function targetLabel(): string
    {
        return $this->classRoom?->displayName()
            ?? $this->course?->title
            ?? 'Lớp học đã xóa';
    }

    public function scheduleLabel(): string
    {
        if ($this->classSchedule) {
            return $this->classSchedule->label();
        }

        return $this->attendance_date?->format('d/m/Y') ?? 'Chưa xác định';
    }
}

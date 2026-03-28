<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlotRegistration extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RECORDED = 'recorded';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_NEEDS_RESELECT = 'needs_reselect';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'slot_registrations';

    protected $fillable = [
        'student_id',
        'subject_id',
        'status',
        'note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_RECORDED => 'Đã ghi nhận',
            self::STATUS_SCHEDULED => 'Đã xếp lớp',
            self::STATUS_NEEDS_RESELECT => 'Cần chọn lại',
            self::STATUS_REJECTED => 'Từ chối',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function choices()
    {
        return $this->hasMany(SlotRegistrationChoice::class);
    }

    public function timeSlots()
    {
        return $this->belongsToMany(
            CourseTimeSlot::class,
            'slot_registration_choices',
            'slot_registration_id',
            'course_time_slot_id'
        )->withPivot('priority')->withTimestamps();
    }
}

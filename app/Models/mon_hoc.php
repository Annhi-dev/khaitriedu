<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'mon_hoc';

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'status',
        'image',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeVisibleOnCatalog(Builder $query): Builder
    {
        $dummyNames = ['Ngoại ngữ - Tin học', 'Bồi dưỡng ngắn hạn', 'Đào tạo nghề', 'Đào tạo dài hạn'];

        return $query->publiclyAvailable()
            ->whereNotIn('name', $dummyNames)
            ->where(function (Builder $subjectQuery) {
                $subjectQuery->whereNull('category_id')
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->active());
        });
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_OPEN => 'Đang mở',
            self::STATUS_CLOSED => 'Đóng đăng ký',
            self::STATUS_ARCHIVED => 'Lưu trữ',
            default => ucfirst((string) $this->status),
        };
    }

    public function durationLabel(): string
    {
        if (! $this->duration) {
            return 'Chưa cấu hình';
        }

        if ($this->duration >= 12) {
            $years = floor($this->duration / 12);
            $months = $this->duration % 12;
            $res = $years . ' năm';
            if ($months > 0) {
                $res .= ' ' . $months . ' tháng';
            }
            return $res;
        }

        return $this->duration . ' tháng';
    }

    public function isOpenForEnrollment(): bool
    {
        $subjectOpen = $this->status === null || $this->status === self::STATUS_OPEN;

        return $subjectOpen
            && (! $this->category || $this->category->isActive());
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'dang_ky', 'subject_id', 'user_id');
    }

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class, 'subject_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function modules()
    {
        return $this->hasManyThrough(Module::class, Course::class, 'subject_id', 'course_id');
    }

    public function timeSlots()
    {
        return $this->hasMany(CourseTimeSlot::class);
    }

    public function slotRegistrations()
    {
        return $this->hasMany(SlotRegistration::class);
    }

    public function customScheduleRequests()
    {
        return $this->hasMany(CustomScheduleRequest::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}

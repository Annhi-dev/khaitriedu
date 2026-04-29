<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HocPhan extends Model
{
    use HasFactory;

    public const STATUS_PUBLISHED = 'published';
    public const STATUS_UNPUBLISHED = 'unpublished';

    protected $table = 'chuong_hoc';

    protected $fillable = [
        'course_id',
        'title',
        'content',
        'session_count',
        'duration',
        'status',
        'position',
    ];

    protected $casts = [
        'session_count' => 'integer',
        'duration' => 'integer',
        'position' => 'integer',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'Đang hiển thị',
            self::STATUS_UNPUBLISHED => 'Đang ẩn',
            default => ucfirst((string) $this->status),
        };
    }

    public function durationLabel(): string
    {
        return $this->duration ? $this->duration . ' phút' : 'Chưa cấu hình';
    }

    public function sessionCount(): ?int
    {
        if (array_key_exists('session_count', $this->attributes) && $this->attributes['session_count'] !== null) {
            return (int) $this->attributes['session_count'];
        }

        return null;
    }

    public function plannedSessionCount(): int
    {
        return $this->sessionCount() ?? $this->lessonCount();
    }

    public function sessionCountLabel(): string
    {
        return $this->plannedSessionCount() . ' buổi';
    }

    public function lessonCount(): int
    {
        if (array_key_exists('lessons_count', $this->attributes) && $this->attributes['lessons_count'] !== null) {
            return (int) $this->attributes['lessons_count'];
        }

        if ($this->relationLoaded('lessons')) {
            return $this->lessons->count();
        }

        return $this->lessons()->count();
    }

    public function lessonCountLabel(): string
    {
        return $this->sessionCountLabel();
    }

    public function learningSummary(): string
    {
        $summary = trim((string) $this->content);

        return $summary !== '' ? $summary : 'Mục tiêu học tập đang được cập nhật.';
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class);
    }

    public function lessons()
    {
        return $this->hasMany(BaiHoc::class, 'module_id')->orderBy('order');
    }

    public function quizzes()
    {
        return $this->hasManyThrough(BaiKiemTra::class, BaiHoc::class, 'module_id', 'lesson_id', 'id', 'id');
    }
}

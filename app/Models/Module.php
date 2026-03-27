<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    public const STATUS_PUBLISHED = 'published';
    public const STATUS_UNPUBLISHED = 'unpublished';

    protected $table = 'chuong_hoc';

    protected $fillable = [
        'course_id',
        'title',
        'content',
        'duration',
        'status',
        'position',
    ];

    protected $casts = [
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

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function quizzes()
    {
        return $this->hasManyThrough(Quiz::class, Lesson::class, 'module_id', 'lesson_id');
    }
}
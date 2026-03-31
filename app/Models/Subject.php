<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Subject extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_ARCHIVED = 'archived';

    protected static array $schemaColumnCache = [];

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

    public static function hasStatusColumn(): bool
    {
        return static::hasTableColumn('status');
    }

    protected static function hasTableColumn(string $column): bool
    {
        $table = (new static())->getTable();
        $cacheKey = $table . '.' . $column;

        if (! array_key_exists($cacheKey, static::$schemaColumnCache)) {
            static::$schemaColumnCache[$cacheKey] = Schema::hasTable($table) && Schema::hasColumn($table, $column);
        }

        return static::$schemaColumnCache[$cacheKey];
    }

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        return static::hasStatusColumn()
            ? $query->where('status', self::STATUS_OPEN)
            : $query;
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
        if (! static::hasStatusColumn()) {
            return 'Dang mo';
        }

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
        if (!$this->duration) return 'Chưa cấu hình';

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
        $subjectOpen = ! static::hasStatusColumn() || $this->status === null || $this->status === self::STATUS_OPEN;

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

    public function modules()
    {
        return $this->hasManyThrough(Module::class, Course::class, 'subject_id', 'course_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(CourseTimeSlot::class);
    }

    public function slotRegistrations()
    {
        return $this->hasMany(SlotRegistration::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}

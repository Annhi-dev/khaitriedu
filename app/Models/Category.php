<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Category extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected static array $schemaColumnCache = [];

    protected $table = 'danh_muc';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'order',
        'status',
        'program',
        'level',
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

    public function scopeActive($query)
    {
        return static::hasStatusColumn()
            ? $query->where('status', self::STATUS_ACTIVE)
            : $query;
    }

    public function isActive(): bool
    {
        return ! static::hasStatusColumn()
            || $this->status === null
            || $this->status === self::STATUS_ACTIVE;
    }

    public function statusLabel(): string
    {
        if (! static::hasStatusColumn()) {
            return 'Hoat dong';
        }

        return match ($this->status) {
            self::STATUS_ACTIVE => 'Hoat dong',
            self::STATUS_INACTIVE => 'Ngung hoat dong',
            default => ucfirst((string) $this->status),
        };
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function defaultSubject()
    {
        return $this->hasOne(Subject::class)->oldestOfMany();
    }

    public function courses()
    {
        return $this->hasManyThrough(Course::class, Subject::class);
    }
}
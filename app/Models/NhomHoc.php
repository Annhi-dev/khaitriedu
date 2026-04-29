<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhomHoc extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

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

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === null || $this->status === self::STATUS_ACTIVE;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Hoat dong',
            self::STATUS_INACTIVE => 'Ngung hoat dong',
            default => ucfirst((string) $this->status),
        };
    }

    public function subjects()
    {
        return $this->hasMany(MonHoc::class, 'category_id');
    }

    public function defaultSubject()
    {
        return $this->hasOne(MonHoc::class, 'category_id')->oldestOfMany();
    }

    public function courses()
    {
        return $this->hasManyThrough(KhoaHoc::class, MonHoc::class, 'category_id', 'subject_id', 'id', 'id');
    }
}

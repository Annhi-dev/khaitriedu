<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhongBan extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'phong_ban';

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Hoạt động',
            self::STATUS_INACTIVE => 'Tạm ngưng',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function users(): HasMany
    {
        return $this->hasMany(NguoiDung::class, 'department_id');
    }

    public function teachers(): HasMany
    {
        return $this->users()->whereHas('role', fn ($query) => $query->where('name', NguoiDung::ROLE_TEACHER));
    }
}

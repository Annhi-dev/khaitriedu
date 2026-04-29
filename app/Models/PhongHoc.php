<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhongHoc extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'rooms';

    protected $fillable = [
        'code',
        'name',
        'type',
        'location',
        'capacity',
        'status',
        'note',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Hoạt động',
            self::STATUS_MAINTENANCE => 'Bảo trì',
            self::STATUS_INACTIVE => 'Ngừng sử dụng',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function timeSlots()
    {
        return $this->hasMany(KhungGioKhoaHoc::class, 'room_id');
    }

    public function classRooms()
    {
        return $this->hasMany(LopHoc::class, 'room_id');
    }
}

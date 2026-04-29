<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuaChonNguyenVongKhungGio extends Model
{
    use HasFactory;

    protected $table = 'slot_registration_choices';

    protected $fillable = [
        'slot_registration_id',
        'course_time_slot_id',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function slotRegistration()
    {
        return $this->belongsTo(NguyenVongKhungGio::class, 'slot_registration_id');
    }

    public function courseTimeSlot()
    {
        return $this->belongsTo(KhungGioKhoaHoc::class, 'course_time_slot_id');
    }
}

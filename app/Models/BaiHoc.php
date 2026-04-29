<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaiHoc extends Model
{
    protected $table = 'bai_hoc';

    use HasFactory;

    protected $fillable = ['module_id', 'title', 'description', 'content', 'order', 'duration', 'video_url'];

    public function module()
    {
        return $this->belongsTo(HocPhan::class, 'module_id');
    }

    public function course()
    {
        return $this->hasOneThrough(KhoaHoc::class, HocPhan::class, 'id', 'id', 'module_id', 'course_id');
    }

    public function quiz()
    {
        return $this->hasOne(BaiKiemTra::class, 'lesson_id');
    }

    public function progress()
    {
        return $this->hasMany(TienDoBaiHoc::class, 'lesson_id');
    }
}

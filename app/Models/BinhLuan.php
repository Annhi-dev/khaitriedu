<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinhLuan extends Model
{
    use HasFactory;

    protected $table = 'binh_luan';

    protected $fillable = ['user_id', 'lesson_id', 'course_id', 'parent_id', 'content', 'likes', 'type'];

    public function user()
    {
        return $this->belongsTo(NguoiDung::class);
    }

    public function lesson()
    {
        return $this->belongsTo(BaiHoc::class);
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class);
    }

    public function parent()
    {
        return $this->belongsTo(BinhLuan::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(BinhLuan::class, 'parent_id');
    }
}

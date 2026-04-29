<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhGia extends Model
{
    protected $table = 'danh_gia';

    protected $fillable = ['user_id', 'course_id', 'rating', 'comment'];

    public function user()
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChungChi extends Model
{
    use HasFactory;

    protected $table = 'chung_chi';

    protected $fillable = ['user_id', 'course_id', 'certificate_number', 'file_path', 'score', 'issued_at', 'expires_at', 'status'];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }
}

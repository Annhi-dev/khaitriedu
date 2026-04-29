<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TepDinhKem extends Model
{
    use HasFactory;

    protected $table = 'tai_lieu_dinh_kem';

    protected $fillable = ['lesson_id', 'quiz_id', 'filename', 'file_path', 'mime_type', 'file_size', 'description'];

    public function lesson()
    {
        return $this->belongsTo(BaiHoc::class);
    }

    public function quiz()
    {
        return $this->belongsTo(BaiKiemTra::class);
    }
}

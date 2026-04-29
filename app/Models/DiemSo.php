<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiemSo extends Model
{
    protected $table = 'diem';

    protected $fillable = [
        'enrollment_id',
        'module_id',
        'class_room_id',
        'student_id',
        'teacher_id',
        'test_name',
        'score',
        'weight',
        'grade',
        'feedback',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'weight' => 'integer',
    ];

    public function enrollment()
    {
        return $this->belongsTo(GhiDanh::class, 'enrollment_id');
    }

    public function module()
    {
        return $this->belongsTo(HocPhan::class, 'module_id');
    }

    public function classRoom()
    {
        return $this->belongsTo(LopHoc::class, 'class_room_id');
    }

    public function student()
    {
        return $this->belongsTo(NguoiDung::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(NguoiDung::class, 'teacher_id');
    }
}

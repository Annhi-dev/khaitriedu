<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YeuCauLichTuyChon extends Model
{
    use HasFactory;

    protected $table = 'custom_schedule_requests';

    protected $fillable = [
        'student_id',
        'subject_id',
        'course_id',
        'preferred_teacher_id',
        'requested_days',
        'requested_time',
        'status',
        'notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'requested_days' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function student()
    {
        return $this->belongsTo(NguoiDung::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(MonHoc::class, 'subject_id');
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }

    public function preferredTeacher()
    {
        return $this->belongsTo(NguoiDung::class, 'preferred_teacher_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(NguoiDung::class, 'reviewed_by');
    }
}

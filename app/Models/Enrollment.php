<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = 'dang_ky';

    use HasFactory;

    protected $fillable = [
        'user_id', 
        'course_id', 
        'preferred_schedule', 
        'assigned_teacher_id', 
        'status', 
        'note', 
        'schedule',
        'start_time',
        'end_time',
        'preferred_days',
        'is_submitted',
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_submitted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }
}

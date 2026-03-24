<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'tien_do_bai_hoc';

    use HasFactory;

    protected $fillable = ['user_id', 'lesson_id', 'is_completed', 'time_spent', 'started_at', 'completed_at'];

    protected $casts = ['is_completed' => 'boolean', 'started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}

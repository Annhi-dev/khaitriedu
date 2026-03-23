<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['subject_id', 'title', 'description', 'schedule', 'teacher_id'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('position');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
}


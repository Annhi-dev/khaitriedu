<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = ['created_by', 'course_id', 'title', 'message', 'is_pinned', 'published_at', 'expires_at', 'status'];

    protected $dates = ['published_at', 'expires_at'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

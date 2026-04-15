<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'cau_hoi';

    protected $fillable = ['quiz_id', 'question', 'description', 'type', 'order', 'points'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class)->orderBy('order');
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }
}

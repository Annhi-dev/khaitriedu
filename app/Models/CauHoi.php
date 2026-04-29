<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CauHoi extends Model
{
    use HasFactory;

    protected $table = 'cau_hoi';

    protected $fillable = ['quiz_id', 'question', 'description', 'type', 'order', 'points'];

    public function quiz()
    {
        return $this->belongsTo(BaiKiemTra::class, 'quiz_id');
    }

    public function test()
    {
        return $this->quiz();
    }

    public function options()
    {
        return $this->hasMany(LuaChon::class, 'question_id')->orderBy('order');
    }

    public function answers()
    {
        return $this->hasMany(TraLoiBaiKiemTra::class, 'question_id');
    }
}

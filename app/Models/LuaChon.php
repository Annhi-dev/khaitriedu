<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LuaChon extends Model
{
    protected $table = 'lua_chon';

    use HasFactory;

    protected $fillable = ['question_id', 'option_text', 'is_correct', 'order'];

    public function question()
    {
        return $this->belongsTo(CauHoi::class, 'question_id');
    }
}

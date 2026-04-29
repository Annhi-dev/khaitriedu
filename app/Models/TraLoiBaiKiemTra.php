<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraLoiBaiKiemTra extends Model
{
    protected $table = 'tra_loi_kiem_tra';

    use HasFactory;

    protected $fillable = ['user_id', 'quiz_id', 'question_id', 'option_id', 'answer_text', 'is_correct', 'attempt'];

    public function user()
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function quiz()
    {
        return $this->belongsTo(BaiKiemTra::class, 'quiz_id');
    }

    public function test()
    {
        return $this->quiz();
    }

    public function question()
    {
        return $this->belongsTo(CauHoi::class, 'question_id');
    }

    public function student()
    {
        return $this->user();
    }

    public function option()
    {
        return $this->belongsTo(LuaChon::class, 'option_id');
    }
}

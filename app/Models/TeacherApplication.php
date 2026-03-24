<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherApplication extends Model
{
    protected $table = 'don_ung_tuyen_giao_vien';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'experience',
        'message',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

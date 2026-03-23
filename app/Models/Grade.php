<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['enrollment_id', 'module_id', 'score', 'grade', 'feedback'];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}

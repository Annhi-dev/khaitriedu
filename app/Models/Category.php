<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'danh_muc';

    protected $fillable = ['name', 'slug', 'description', 'image_path', 'order'];

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}

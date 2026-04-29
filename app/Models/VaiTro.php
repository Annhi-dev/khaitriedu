<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaiTro extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(NguoiDung::class);
    }

    public static function idByName(string $name): int
    {
        static $cache = [];

        if (! isset($cache[$name])) {
            $cache[$name] = static::where('name', $name)->firstOrFail()->id;
        }

        return $cache[$name];
    }
}

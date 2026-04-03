<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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

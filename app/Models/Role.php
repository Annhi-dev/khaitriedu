<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name'];

    // ─── Role Name Constants ────────────────────────
    public const ADMIN = 'admin';
    public const TEACHER = 'teacher';
    public const STUDENT = 'student';

    // ─── Relationships ──────────────────────────────
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ─── Helpers ────────────────────────────────────
    /**
     * Get role ID by name (cached per-request).
     */
    public static function idByName(string $name): int
    {
        static $cache = [];

        if (! isset($cache[$name])) {
            $cache[$name] = static::where('name', $name)->firstOrFail()->id;
        }

        return $cache[$name];
    }
}

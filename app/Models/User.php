<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // ─── Role Constants (English, matching roles table) ──
    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_STUDENT = 'student';

    // ─── Status Constants ────────────────────────────────
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_LOCKED = 'locked';

    protected $table = 'nguoi_dung';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'role_id',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Role Relationship ───────────────────────────────
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // ─── Role Helpers ────────────────────────────────────
    /**
     * Check if user has one of the given roles.
     *
     * Usage: $user->hasRole('admin') or $user->hasRole('admin', 'teacher')
     */
    public function hasRole(string ...$roles): bool
    {
        return $this->role && in_array($this->role->name, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(self::ROLE_TEACHER);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(self::ROLE_STUDENT);
    }

    /**
     * Get role name as string (for display/backward compat).
     */
    public function getRoleName(): string
    {
        return $this->role?->name ?? 'unknown';
    }

    /**
     * Get human-readable role label.
     */
    public function roleLabel(): string
    {
        return match ($this->role?->name) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_TEACHER => 'Giảng viên',
            self::ROLE_STUDENT => 'Học viên',
            default => ucfirst($this->role?->name ?? 'Unknown'),
        };
    }

    // ─── Scopes ──────────────────────────────────────────
    public function scopeStudents($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', self::ROLE_STUDENT));
    }

    public function scopeTeachers($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', self::ROLE_TEACHER));
    }

    // ─── Status Helpers ──────────────────────────────────
    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Hoat dong',
            self::STATUS_INACTIVE => 'Tam dung',
            self::STATUS_LOCKED => 'Da khoa',
            default => ucfirst((string) $this->status),
        };
    }

    // ─── Relationships ───────────────────────────────────
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function grades()
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    public function recordedAttendances()
    {
        return $this->hasMany(AttendanceRecord::class, 'teacher_id');
    }

    public function taughtCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function scheduleChangeRequests()
    {
        return $this->hasMany(ScheduleChangeRequest::class, 'teacher_id');
    }
}
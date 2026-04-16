<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_STUDENT = 'student';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_LOCKED = 'locked';

    protected $table = 'nguoi_dung';

    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'avatar_path',
        'password',
        'role_id',
        'department_id',
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

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

    public function getRoleName(): string
    {
        return $this->role?->name ?? 'unknown';
    }

    public function roleLabel(): string
    {
        return match ($this->role?->name) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_TEACHER => 'Giảng viên',
            self::ROLE_STUDENT => 'Học viên',
            default => ucfirst($this->role?->name ?? 'Unknown'),
        };
    }

    public function scopeStudents($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', self::ROLE_STUDENT));
    }

    public function scopeTeachers($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', self::ROLE_TEACHER));
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Hoạt động',
            self::STATUS_INACTIVE => 'Tạm dừng',
            self::STATUS_LOCKED => 'Đã khóa',
            default => ucfirst((string) $this->status),
        };
    }

    public function displayName(): string
    {
        $name = trim((string) $this->name);

        if (preg_match('/^(.*?)(?:\s*\(([^()]*)\))\s*$/u', $name, $matches)) {
            $displayName = trim($matches[1]);

            return $displayName !== '' ? $displayName : $name;
        }

        return $name;
    }

    public function specialtyLabel(): ?string
    {
        $name = trim((string) $this->name);

        if (preg_match('/\(([^()]*)\)\s*$/u', $name, $matches)) {
            $specialty = trim($matches[1]);

            return $specialty !== '' ? $specialty : null;
        }

        return null;
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function classEnrollments()
    {
        return $this->hasMany(Enrollment::class)->whereNotNull('lop_hoc_id');
    }

    public function enrolledClasses()
    {
        return $this->belongsToMany(ClassRoom::class, 'dang_ky', 'user_id', 'lop_hoc_id')
            ->withPivot([
                'id',
                'subject_id',
                'course_id',
                'status',
                'assigned_teacher_id',
                'schedule',
                'submitted_at',
            ])
            ->withTimestamps();
    }

    public function customScheduleRequests()
    {
        return $this->hasMany(CustomScheduleRequest::class, 'student_id');
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

    public function academicGrades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public function recordedAttendances()
    {
        return $this->hasMany(AttendanceRecord::class, 'teacher_id');
    }

    public function taughtCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function teachingClassRooms()
    {
        return $this->hasMany(ClassRoom::class, 'teacher_id');
    }

    public function teachingSchedules()
    {
        return $this->hasMany(ClassSchedule::class, 'teacher_id');
    }

    public function scheduleChangeRequests()
    {
        return $this->hasMany(ScheduleChangeRequest::class, 'teacher_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function teacherEvaluations()
    {
        return $this->hasMany(TeacherEvaluation::class, 'teacher_id');
    }

    public function receivedTeacherEvaluations()
    {
        return $this->hasMany(TeacherEvaluation::class, 'student_id');
    }

    public function avatarUrl(): ?string
    {
        $path = trim((string) $this->avatar_path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

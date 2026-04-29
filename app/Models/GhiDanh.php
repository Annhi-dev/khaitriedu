<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GhiDanh extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ENROLLED = 'enrolled';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const LEGACY_STATUS_CONFIRMED = 'confirmed';
    public const REQUEST_SOURCE_CUSTOM_SCHEDULE = 'custom_schedule';
    public const REQUEST_SOURCE_FIXED_CLASS = 'fixed_class';

    protected $table = 'dang_ky';

    protected $fillable = [
        'user_id',
        'subject_id',
        'course_id',
        'lop_hoc_id',
        'preferred_schedule',
        'assigned_teacher_id',
        'status',
        'note',
        'schedule',
        'start_time',
        'end_time',
        'preferred_days',
        'is_submitted',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_submitted' => 'boolean',
        'preferred_days' => 'array',
    ];

    public static function filterableStatuses(): array
    {
        return array_keys(self::statusOptions());
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Chờ duyệt',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            self::STATUS_ENROLLED => 'Đã ghi danh',
            self::STATUS_SCHEDULED => 'Đã xếp lớp',
            self::STATUS_ACTIVE => 'Đang học',
            self::STATUS_COMPLETED => 'Hoàn thành',
        ];
    }

    public static function courseAccessStatuses(): array
    {
        return [
            self::LEGACY_STATUS_CONFIRMED,
            self::STATUS_ENROLLED,
            self::STATUS_SCHEDULED,
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
        ];
    }

    public static function scheduleBlockingStatuses(): array
    {
        return [
            self::LEGACY_STATUS_CONFIRMED,
            self::STATUS_ENROLLED,
            self::STATUS_SCHEDULED,
            self::STATUS_ACTIVE,
        ];
    }

    public static function requestSourceOptions(): array
    {
        return [
            self::REQUEST_SOURCE_CUSTOM_SCHEDULE => 'Yêu cầu lịch học riêng',
            self::REQUEST_SOURCE_FIXED_CLASS => 'Ghi danh lớp cố định',
        ];
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('is_submitted', true);
    }

    public function scopeForUserSubject(Builder $query, int $userId, int $subjectId): Builder
    {
        return $query->where('user_id', $userId)
            ->where(function (Builder $builder) use ($subjectId) {
                $builder->where('subject_id', $subjectId)
                    ->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->where('subject_id', $subjectId));
            });
    }

    public function normalizedStatus(): string
    {
        return $this->status === self::LEGACY_STATUS_CONFIRMED
            ? self::STATUS_SCHEDULED
            : (string) $this->status;
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->normalizedStatus()] ?? ucfirst((string) $this->status);
    }

    public function displayStatus(): string
    {
        $displayStatus = $this->getAttribute('display_status');

        if (is_string($displayStatus) && $displayStatus !== '') {
            return $displayStatus;
        }

        if ($this->lop_hoc_id !== null) {
            return self::resolveUnifiedStatusForClassGroup(collect([$this]), $this->classRoom);
        }

        return $this->normalizedStatus();
    }

    public function displayStatusLabel(): string
    {
        return self::statusOptions()[$this->displayStatus()] ?? ucfirst((string) $this->displayStatus());
    }

    public static function syncDisplayStatusesByClass(Collection $enrollments): Collection
    {
        $enrollments->each(function (GhiDanh $enrollment): void {
            $enrollment->setAttribute('display_status', $enrollment->normalizedStatus());
        });

        $classGroups = $enrollments
            ->filter(fn (GhiDanh $enrollment) => $enrollment->lop_hoc_id !== null)
            ->groupBy(fn (GhiDanh $enrollment) => (int) $enrollment->lop_hoc_id);

        foreach ($classGroups as $group) {
            $classRoom = $group->first()?->classRoom;
            $unifiedStatus = self::resolveUnifiedStatusForClassGroup($group, $classRoom);

            foreach ($group as $enrollment) {
                $enrollment->setAttribute('display_status', $unifiedStatus);
            }
        }

        return $enrollments;
    }

    protected static function resolveUnifiedStatusForClassGroup(Collection $group, ?LopHoc $classRoom = null): string
    {
        if ($classRoom) {
            if (in_array($classRoom->status, [LopHoc::STATUS_COMPLETED, LopHoc::STATUS_CLOSED], true)) {
                return self::STATUS_COMPLETED;
            }

            return self::STATUS_ACTIVE;
        }

        $statuses = $group
            ->map(fn (GhiDanh $enrollment) => $enrollment->normalizedStatus())
            ->values();

        if ($statuses->isEmpty()) {
            return self::STATUS_ENROLLED;
        }

        if ($statuses->every(fn (string $status) => $status === self::STATUS_COMPLETED)) {
            return self::STATUS_COMPLETED;
        }

        foreach ([
            self::STATUS_ACTIVE,
            self::STATUS_SCHEDULED,
            self::STATUS_ENROLLED,
            self::STATUS_APPROVED,
            self::STATUS_PENDING,
            self::STATUS_REJECTED,
        ] as $status) {
            if ($statuses->contains($status)) {
                return $status;
            }
        }

        return (string) $statuses->first();
    }

    public function requestSourceKey(): string
    {
        return $this->isFixedClassEnrollment()
            ? self::REQUEST_SOURCE_FIXED_CLASS
            : self::REQUEST_SOURCE_CUSTOM_SCHEDULE;
    }

    public function requestSourceLabel(): string
    {
        return self::requestSourceOptions()[$this->requestSourceKey()] ?? 'Chưa phân loại';
    }

    public function requestSourceBadgeType(): string
    {
        return $this->isFixedClassEnrollment() ? 'success' : 'info';
    }

    public function isFixedClassEnrollment(): bool
    {
        return $this->lop_hoc_id !== null
            || $this->normalizedStatus() === self::STATUS_ENROLLED;
    }

    public function isCustomScheduleRequest(): bool
    {
        return ! $this->isFixedClassEnrollment();
    }

    public function hasCourseAccess(): bool
    {
        return in_array($this->normalizedStatus(), self::courseAccessStatuses(), true);
    }

    public function user()
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function student()
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(NguoiDung::class, 'assigned_teacher_id');
    }

    public function teacher()
    {
        return $this->assignedTeacher();
    }

    public function reviewer()
    {
        return $this->belongsTo(NguoiDung::class, 'reviewed_by');
    }

    public function subject()
    {
        return $this->belongsTo(MonHoc::class, 'subject_id');
    }

    public function classRoom()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function lopHoc()
    {
        return $this->classRoom();
    }

    public function currentClassRoom(): ?LopHoc
    {
        if ($this->classRoom) {
            return $this->classRoom;
        }

        $historicalClassRoom = $this->historicalClassRoom();

        if ($historicalClassRoom) {
            return $historicalClassRoom;
        }

        return $this->course?->currentClassRoom();
    }

    public function conflictReferenceClassRoom(): ?LopHoc
    {
        if ($this->classRoom) {
            return $this->classRoom;
        }

        $historicalClassRoom = $this->historicalClassRoom();

        if ($historicalClassRoom) {
            return $historicalClassRoom;
        }

        $course = $this->course;

        if (! $course) {
            return null;
        }

        $referenceClassRoom = new LopHoc([
            'subject_id' => $this->subject_id ?? $course->subject_id,
            'course_id' => $course->id,
            'name' => $course->title,
            'teacher_id' => $this->assigned_teacher_id ?? $course->teacher_id,
            'start_date' => $course->start_date,
            'duration' => $course->subject?->duration,
            'status' => $this->normalizedStatus() === self::STATUS_COMPLETED
                ? LopHoc::STATUS_COMPLETED
                : LopHoc::STATUS_OPEN,
        ]);

        $referenceClassRoom->setRelation('course', $course);

        return $referenceClassRoom;
    }

    public function historicalClassRoom(): ?LopHoc
    {
        $course = $this->course;

        if (! $course) {
            return null;
        }

        $course->loadMissing(['classRooms.room', 'classRooms.teacher', 'classRooms.schedules']);

        $classRooms = $course->classRooms;

        if ($classRooms->isEmpty()) {
            return null;
        }

        if ($classRooms->count() === 1) {
            return $classRooms->first();
        }

        $storedSchedule = $this->normalizedScheduleSignature($this->schedule);

        if ($storedSchedule !== null) {
            $matchedClassRoom = $classRooms->first(function (LopHoc $classRoom) use ($storedSchedule): bool {
                return $this->normalizedScheduleSignature($classRoom->scheduleSummary()) === $storedSchedule;
            });

            if ($matchedClassRoom) {
                return $matchedClassRoom;
            }
        }

        if ($this->assigned_teacher_id !== null) {
            $teacherMatches = $classRooms
                ->filter(fn (LopHoc $classRoom) => (int) $classRoom->teacher_id === (int) $this->assigned_teacher_id)
                ->values();

            if ($teacherMatches->count() === 1) {
                return $teacherMatches->first();
            }

            if ($storedSchedule !== null) {
                $matchedClassRoom = $teacherMatches->first(function (LopHoc $classRoom) use ($storedSchedule): bool {
                    return $this->normalizedScheduleSignature($classRoom->scheduleSummary()) === $storedSchedule;
                });

                if ($matchedClassRoom) {
                    return $matchedClassRoom;
                }
            }
        }

        if ($this->normalizedStatus() === self::STATUS_COMPLETED) {
            $completedClassRooms = $classRooms
                ->filter(fn (LopHoc $classRoom) => in_array($classRoom->status, [LopHoc::STATUS_COMPLETED, LopHoc::STATUS_CLOSED], true))
                ->values();

            if ($completedClassRooms->count() === 1) {
                return $completedClassRooms->first();
            }
        }

        return null;
    }

    protected function normalizedScheduleSignature(?string $value): ?string
    {
        $normalized = Str::squish((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    public function currentClassRoomLabel(): string
    {
        return $this->currentClassRoom()?->displayName()
            ?? $this->course?->title
            ?? 'Chưa xếp lớp';
    }

    public function grades()
    {
        return $this->hasMany(DiemSo::class, 'enrollment_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(DiemDanh::class, 'enrollment_id');
    }

    public function evaluations()
    {
        $query = $this->hasMany(DanhGiaGiaoVien::class, 'student_id', 'user_id');

        if ($this->lop_hoc_id !== null) {
            $query->where('class_room_id', $this->lop_hoc_id);
        }

        return $query;
    }
}

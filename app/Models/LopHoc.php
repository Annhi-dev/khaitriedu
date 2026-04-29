<?php

namespace App\Models;

use App\Helpers\ScheduleHelper;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LopHoc extends Model
{
    use HasFactory;

    public const STATUS_OPEN      = 'open';
    public const STATUS_FULL      = 'full';
    public const STATUS_CLOSED    = 'closed';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'lop_hoc';

    protected $fillable = [
        'subject_id',
        'course_id',
        'name',
        'room_id',
        'teacher_id',
        'start_date',
        'duration',
        'status',
        'note',
        'grade_weights',
    ];

    protected $casts = [
        'start_date' => 'date',
        'duration'   => 'integer',
        'grade_weights' => 'array',
    ];


    public function subject()
    {
        return $this->belongsTo(MonHoc::class, 'subject_id');
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }

    public function room()
    {
        return $this->belongsTo(PhongHoc::class, 'room_id');
    }

    public function teacher()
    {
        return $this->belongsTo(NguoiDung::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(LichHoc::class, 'lop_hoc_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(DiemDanh::class, 'class_room_id');
    }

    public function grades()
    {
        return $this->hasMany(DiemSo::class, 'class_room_id');
    }

    public function evaluations()
    {
        return $this->hasMany(DanhGiaGiaoVien::class, 'class_room_id');
    }

    public function scheduleChangeRequests()
    {
        return $this->hasMany(YeuCauDoiLich::class, 'class_room_id');
    }

    public function enrollments()
    {
        return $this->hasMany(GhiDanh::class, 'lop_hoc_id');
    }

    public function quizzes()
    {
        return $this->hasMany(BaiKiemTra::class, 'lop_hoc_id');
    }

    public function students()
    {
        return $this->hasManyThrough(
            NguoiDung::class,
            GhiDanh::class,
            'lop_hoc_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function enrolledStudents()
    {
        return $this->belongsToMany(NguoiDung::class, 'dang_ky', 'lop_hoc_id', 'user_id')
            ->withPivot([
                'id',
                'subject_id',
                'course_id',
                'status',
                'assigned_teacher_id',
                'schedule',
            ])
            ->wherePivotIn('status', GhiDanh::courseAccessStatuses())
            ->withTimestamps();
    }


    public function enrolledCount(): int
    {
        if ($this->getAttribute('enrollments_count') !== null) {
            return (int) $this->getAttribute('enrollments_count');
        }

        if ($this->relationLoaded('enrollments')) {
            return $this->enrollments->count();
        }

        return $this->enrollments()->count();
    }

    public function isFull(): bool
    {
        if (! $this->room) {
            return false;
        }

        return $this->enrolledCount() >= $this->room->capacity;
    }

    public function availableSlots(): int
    {
        if (! $this->room) {
            return 999;
        }

        return max(0, $this->room->capacity - $this->enrolledCount());
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN      => 'Đang mở',
            self::STATUS_FULL      => 'Đã đủ chỗ',
            self::STATUS_CLOSED    => 'Đã đóng',
            self::STATUS_COMPLETED => 'Hoàn thành',
            default                => ucfirst((string) $this->status),
        };
    }

    public function displayName(): string
    {
        $subjectName = $this->name
            ?: $this->course?->title
            ?: (($this->subject?->name ?? 'Lớp học') . ' - Lớp ' . $this->id);
        $roomName = $this->room?->name;

        return trim($subjectName . ($roomName ? ' (' . $roomName . ')' : ''));
    }

    public function scheduleSummary(): string
    {
        $segments = $this->schedules
            ->sortBy(function (LichHoc $schedule) {
                return array_search($schedule->day_of_week, array_keys(LichHoc::$dayOptions), true);
            })
            ->map(fn (LichHoc $schedule) => $schedule->label())
            ->values();

        return $segments->isNotEmpty()
            ? $segments->implode(' | ')
            : 'Chưa có lịch giảng dạy';
    }

    public function scheduleRows(): Collection
    {
        return $this->effectiveScheduleRows();
    }

    public function conflictsWith(LopHoc $other): bool
    {
        return $this->firstScheduleConflictWith($other) !== null;
    }

    public function firstScheduleConflictWith(LopHoc $other): ?array
    {
        $thisSchedules = $this->scheduleRows();
        $otherSchedules = $other->scheduleRows();

        if ($thisSchedules->isEmpty() || $otherSchedules->isEmpty()) {
            return null;
        }

        $thisStartDate = $this->scheduleRangeStart();
        $thisEndDate = $this->scheduleRangeEnd();
        $otherStartDate = $other->scheduleRangeStart();
        $otherEndDate = $other->scheduleRangeEnd();

        foreach ($thisSchedules as $thisSchedule) {
            foreach ($otherSchedules as $otherSchedule) {
                if (($thisSchedule['day_of_week'] ?? null) !== ($otherSchedule['day_of_week'] ?? null)) {
                    continue;
                }

                $thisStartTime = substr((string) ($thisSchedule['start_time'] ?? ''), 0, 5);
                $thisEndTime = substr((string) ($thisSchedule['end_time'] ?? ''), 0, 5);
                $otherStartTime = substr((string) ($otherSchedule['start_time'] ?? ''), 0, 5);
                $otherEndTime = substr((string) ($otherSchedule['end_time'] ?? ''), 0, 5);

                if ($thisStartTime >= $otherEndTime || $thisEndTime <= $otherStartTime) {
                    continue;
                }

                if ($thisStartDate && $thisEndDate && $otherStartDate && $otherEndDate) {
                    if (! ($thisStartDate->lte($otherEndDate) && $thisEndDate->gte($otherStartDate))) {
                        continue;
                    }
                }

                return [
                    'day_of_week' => (string) ($thisSchedule['day_of_week'] ?? ''),
                    'day_label' => LichHoc::$dayOptions[$thisSchedule['day_of_week'] ?? ''] ?? (string) ($thisSchedule['day_of_week'] ?? ''),
                    'existing_start_time' => $thisStartTime,
                    'existing_end_time' => $thisEndTime,
                    'existing_time_label' => ScheduleHelper::timeRangeLabel($thisStartTime, $thisEndTime),
                    'candidate_start_time' => $otherStartTime,
                    'candidate_end_time' => $otherEndTime,
                    'candidate_time_label' => ScheduleHelper::timeRangeLabel($otherStartTime, $otherEndTime),
                ];
            }
        }

        return null;
    }

    public function scheduleRangeStart(): ?CarbonInterface
    {
        return $this->start_date?->copy()->startOfDay()
            ?? $this->course?->start_date?->copy()->startOfDay();
    }

    public function scheduleRangeEnd(): ?CarbonInterface
    {
        if ($this->course?->end_date) {
            return $this->course->end_date->copy()->endOfDay();
        }

        $startDate = $this->scheduleRangeStart();

        if (! $startDate) {
            return null;
        }

        $months = max(1, (int) ($this->duration ?? $this->course?->subject?->duration ?? 1));

        return $startDate->copy()->addMonths($months)->endOfDay();
    }

    public function enrollmentAvailabilityState(?CarbonInterface $reference = null): string
    {
        if ($this->status !== self::STATUS_OPEN) {
            return 'closed';
        }

        $reference = $reference
            ? CarbonImmutable::instance($reference->toImmutable())
            : CarbonImmutable::now();

        $startDate = $this->scheduleRangeStart();
        $endDate = $this->scheduleRangeEnd();

        if ($endDate && $reference->startOfDay()->gt($endDate->endOfDay())) {
            return 'ended';
        }

        if ($startDate && $reference->startOfDay()->gte($startDate->startOfDay())) {
            return 'started';
        }

        if ($this->isFull()) {
            return 'full';
        }

        return 'available';
    }

    public function enrollmentAvailabilityLabel(?CarbonInterface $reference = null): string
    {
        return match ($this->enrollmentAvailabilityState($reference)) {
            'available' => 'Có thể đăng ký',
            'started' => 'Đã bắt đầu',
            'ended' => 'Đã kết thúc',
            'full' => 'Đã đủ chỗ',
            'closed' => 'Đã đóng đăng ký',
            default => 'Không thể đăng ký',
        };
    }

    public function enrollmentBlockReason(?CarbonInterface $reference = null): ?string
    {
        return match ($this->enrollmentAvailabilityState($reference)) {
            'started' => 'Lớp học này đã bắt đầu, không thể đăng ký.',
            'ended' => 'Lớp học này đã kết thúc, vui lòng chờ admin mở lớp mới.',
            'full' => 'Lớp học này đã đủ chỗ, vui lòng chọn lớp khác.',
            'closed' => 'Lớp học này hiện không mở đăng ký.',
            default => null,
        };
    }

    public function canAcceptEnrollment(?CarbonInterface $reference = null): bool
    {
        return $this->enrollmentAvailabilityState($reference) === 'available';
    }

    public function enrollmentAvailabilitySortKey(?CarbonInterface $reference = null): string
    {
        $state = $this->enrollmentAvailabilityState($reference);
        $stateWeight = match ($state) {
            'available' => 0,
            'full' => 1,
            'started' => 2,
            'ended' => 3,
            'closed' => 4,
            default => 5,
        };

        $startTimestamp = $this->scheduleRangeStart()?->timestamp ?? PHP_INT_MAX;

        return sprintf('%02d-%020d-%06d', $stateWeight, $startTimestamp, (int) $this->id);
    }

    public function overlapsDateRange(CarbonInterface $candidateStartDate, CarbonInterface $candidateEndDate): bool
    {
        $existingStartDate = $this->scheduleRangeStart();
        $existingEndDate = $this->scheduleRangeEnd();

        if (! $existingStartDate || ! $existingEndDate) {
            return true;
        }

        return $existingStartDate->lte($candidateEndDate) && $existingEndDate->gte($candidateStartDate);
    }

    protected static function normalizeDateRange(?string $startDate, ?string $endDate): ?array
    {
        if (! $startDate || ! $endDate) {
            return null;
        }

        return [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ];
    }

    protected function effectiveScheduleRows(): Collection
    {
        $this->loadMissing(['schedules', 'course']);

        if ($this->schedules->isNotEmpty()) {
            return $this->schedules->map(fn (LichHoc $schedule) => [
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ])->values();
        }

        $meetingDays = $this->course?->meetingDayValues() ?? [];

        if ($meetingDays === [] || ! $this->course?->start_time || ! $this->course?->end_time) {
            return collect();
        }

        return collect($meetingDays)->map(fn (string $dayOfWeek) => [
            'day_of_week' => $dayOfWeek,
            'start_time' => $this->course?->start_time,
            'end_time' => $this->course?->end_time,
        ])->values();
    }


    
    public static function teacherHasConflict(
        int $teacherId,
        array $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $excludeId = null
    ): bool {
        $candidateRange = static::normalizeDateRange($startDate, $endDate);

        return static::query()
            ->where('teacher_id', $teacherId)
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CLOSED])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereHas('schedules', function ($q) use ($dayOfWeek, $startTime, $endTime) {
                $q->whereIn('day_of_week', $dayOfWeek)
                  ->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->with(['course.subject'])
            ->get()
            ->contains(function (LopHoc $classRoom) use ($candidateRange) {
                if ($candidateRange === null) {
                    return true;
                }

                return $classRoom->overlapsDateRange($candidateRange[0], $candidateRange[1]);
            });
    }

    
    public static function roomHasConflict(
        int $roomId,
        array $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $excludeId = null
    ): bool {
        $candidateRange = static::normalizeDateRange($startDate, $endDate);

        return static::query()
            ->where('room_id', $roomId)
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CLOSED])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereHas('schedules', function ($q) use ($dayOfWeek, $startTime, $endTime) {
                $q->whereIn('day_of_week', $dayOfWeek)
                  ->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->with(['course.subject'])
            ->get()
            ->contains(function (LopHoc $classRoom) use ($candidateRange) {
                if ($candidateRange === null) {
                    return true;
                }

                return $classRoom->overlapsDateRange($candidateRange[0], $candidateRange[1]);
            });
    }
}

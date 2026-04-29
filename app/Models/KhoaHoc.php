<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhoaHoc extends Model
{
    use HasFactory;

    public const DEFAULT_CAPACITY = 30;
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_OPEN = 'pending_open';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'khoa_hoc';

    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'price',
        'schedule',
        'teacher_id',
        'day_of_week',
        'meeting_days',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'capacity',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'meeting_days' => 'array',
        'capacity' => 'integer',
    ];

    public static function dayOptions(): array
    {
        return [
            'Monday' => 'Thứ 2',
            'Tuesday' => 'Thứ 3',
            'Wednesday' => 'Thứ 4',
            'Thursday' => 'Thứ 5',
            'Friday' => 'Thứ 6',
            'Saturday' => 'Thứ 7',
            'Sunday' => 'Chủ nhật',
        ];
    }

    public static function schedulingStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED,
            self::STATUS_ACTIVE,
        ];
    }

    public static function minimumStudentsToOpen(): int
    {
        return 5;
    }

    public static function defaultCapacity(): int
    {
        return self::DEFAULT_CAPACITY;
    }

    public function subject()
    {
        return $this->belongsTo(MonHoc::class, 'subject_id');
    }

    public function modules()
    {
        return $this->hasMany(HocPhan::class, 'course_id')->orderBy('position');
    }

    public function enrollments()
    {
        return $this->hasMany(GhiDanh::class, 'course_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(DiemDanh::class, 'course_id');
    }

    public function teacher()
    {
        return $this->belongsTo(NguoiDung::class, 'teacher_id');
    }

    public function reviews()
    {
        return $this->hasMany(DanhGia::class, 'course_id');
    }

    public function scheduleChangeRequests()
    {
        return $this->hasMany(YeuCauDoiLich::class, 'course_id');
    }

    public function classRooms()
    {
        return $this->hasMany(LopHoc::class, 'course_id');
    }

    public function quizzes()
    {
        return $this->hasMany(BaiKiemTra::class, 'course_id');
    }

    public function currentClassRoom(): ?LopHoc
    {
        $classRooms = $this->relationLoaded('classRooms')
            ? $this->classRooms
            : $this->classRooms()->with(['room', 'teacher', 'schedules'])->orderByDesc('id')->get();

        if ($classRooms->isEmpty()) {
            return null;
        }

        return $classRooms
            ->sortByDesc('id')
            ->first(function (LopHoc $classRoom) {
                return ! in_array($classRoom->status, [LopHoc::STATUS_CLOSED, LopHoc::STATUS_COMPLETED], true);
            })
            ?? $classRooms->sortByDesc('id')->first();
    }

    public function customScheduleRequests()
    {
        return $this->hasMany(YeuCauLichTuyChon::class);
    }

    public function lessons()
    {
        return $this->hasManyThrough(BaiHoc::class, HocPhan::class)->orderBy('order');
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function dayLabel(): string
    {
        $firstDay = $this->meetingDayValues()[0] ?? $this->day_of_week;

        return self::dayOptions()[$firstDay] ?? 'Chưa chọn ngày';
    }

    public function meetingDayValues(): array
    {
        if (is_array($this->meeting_days) && $this->meeting_days !== []) {
            return array_values(array_unique(array_filter(array_map(
                fn ($day) => is_string($day) ? trim($day) : null,
                $this->meeting_days
            ))));
        }

        return $this->day_of_week ? [(string) $this->day_of_week] : [];
    }

    public function meetingDaysLabel(): string
    {
        $labels = array_map(
            fn (string $day) => self::dayOptions()[$day] ?? $day,
            $this->meetingDayValues()
        );

        return $labels !== [] ? implode(', ', $labels) : 'Chưa chọn ngày';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_PENDING_OPEN => 'Chờ mở lớp',
            self::STATUS_SCHEDULED => 'Đã xếp lịch',
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_ARCHIVED => 'Lưu trữ',
            default => ucfirst((string) $this->status),
        };
    }

    public function isPendingOpen(): bool
    {
        return $this->status === self::STATUS_PENDING_OPEN;
    }

    public function formattedSchedule(): string
    {
        if ($this->schedule) {
            return $this->schedule;
        }

        $segments = [];

        if ($this->meetingDayValues() !== []) {
            $segments[] = $this->meetingDaysLabel();
        }

        if ($this->start_time && $this->end_time) {
            $segments[] = $this->start_time . ' - ' . $this->end_time;
        }

        if ($this->start_date) {
            $segments[] = 'Từ ' . $this->start_date->format('d/m/Y') . ($this->end_date ? ' đến ' . $this->end_date->format('d/m/Y') : '');
        }

        return $segments ? implode(' | ', $segments) : 'Chưa có lịch cụ thể';
    }

    public function getNameAttribute(): string
    {
        return (string) $this->title;
    }
}

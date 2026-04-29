<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Models\LopHoc;
use App\Models\KhoaHoc;
use App\Models\MonHoc;
use App\Models\NguoiDung;

class BaiKiemTra extends Model
{
    protected $table = 'bai_kiem_tra';

    use HasFactory;

    protected $attributes = [
        'status' => self::STATUS_PUBLISHED,
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'teacher_id',
        'course_id',
        'subject_id',
        'lop_hoc_id',
        'lesson_id',
        'title',
        'description',
        'duration_minutes',
        'total_score',
        'status',
        'published_at',
        'passing_score',
        'is_required',
        'max_attempts',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'total_score' => 'decimal:2',
        'published_at' => 'datetime',
        'is_required' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(BaiHoc::class, 'lesson_id');
    }

    public function teacher()
    {
        return $this->belongsTo(NguoiDung::class, 'teacher_id');
    }

    public function course()
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }

    public function subject()
    {
        return $this->belongsTo(MonHoc::class, 'subject_id');
    }

    public function classRoom()
    {
        return $this->belongsTo(LopHoc::class, 'lop_hoc_id');
    }

    public function questions()
    {
        return $this->hasMany(CauHoi::class, 'quiz_id')->orderBy('order');
    }

    public function answers()
    {
        return $this->hasMany(TraLoiBaiKiemTra::class, 'quiz_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        if (! static::hasColumn('status')) {
            return $query;
        }

        return $query->where(function (Builder $builder): void {
            $builder->where('status', self::STATUS_PUBLISHED)
                ->orWhereNull('status');
        });
    }

    public function scopeDraft(Builder $query): Builder
    {
        if (! static::hasColumn('status')) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeOwnedByTeacher(Builder $query, NguoiDung|int $teacher): Builder
    {
        $teacherId = $teacher instanceof NguoiDung ? $teacher->id : $teacher;

        return $query->where(function (Builder $builder) use ($teacherId): void {
            $applied = false;

            if (static::hasColumn('teacher_id')) {
                $builder->where('teacher_id', $teacherId);
                $applied = true;
            }

            if (static::hasColumn('course_id')) {
                $applied
                    ? $builder->orWhereHas('course', fn (Builder $courseQuery) => $courseQuery->where('teacher_id', $teacherId))
                    : $builder->whereHas('course', fn (Builder $courseQuery) => $courseQuery->where('teacher_id', $teacherId));
                $applied = true;
            }

            if (static::hasColumn('lesson_id')) {
                $applied
                    ? $builder->orWhereHas('lesson', function (Builder $lessonQuery) use ($teacherId): void {
                        $lessonQuery->whereHas('module.course', fn (Builder $courseQuery) => $courseQuery->where('teacher_id', $teacherId));
                    })
                    : $builder->whereHas('lesson', function (Builder $lessonQuery) use ($teacherId): void {
                        $lessonQuery->whereHas('module.course', fn (Builder $courseQuery) => $courseQuery->where('teacher_id', $teacherId));
                    });
                $applied = true;
            }

            if (static::hasColumn('lop_hoc_id')) {
                $applied
                    ? $builder->orWhereHas('classRoom', fn (Builder $classRoomQuery) => $classRoomQuery->where('teacher_id', $teacherId))
                    : $builder->whereHas('classRoom', fn (Builder $classRoomQuery) => $classRoomQuery->where('teacher_id', $teacherId));
                $applied = true;
            }

            if (! $applied) {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function scopeForCourse(Builder $query, KhoaHoc $course): Builder
    {
        return $query->where(function (Builder $builder) use ($course): void {
            $applied = false;

            if (static::hasColumn('course_id')) {
                $builder->where('course_id', $course->id);
                $applied = true;
            }

            if (static::hasColumn('subject_id')) {
                $applied
                    ? $builder->orWhere('subject_id', $course->subject_id)
                    : $builder->where('subject_id', $course->subject_id);
                $applied = true;
            }

            if (static::hasColumn('lop_hoc_id')) {
                $applied
                    ? $builder->orWhereHas('classRoom', fn (Builder $classRoomQuery) => $classRoomQuery->where('course_id', $course->id))
                    : $builder->whereHas('classRoom', fn (Builder $classRoomQuery) => $classRoomQuery->where('course_id', $course->id));
                $applied = true;
            }

            if (static::hasColumn('lesson_id')) {
                $applied
                    ? $builder->orWhereHas('lesson', function (Builder $lessonQuery) use ($course): void {
                        $lessonQuery->whereHas('module', fn (Builder $moduleQuery) => $moduleQuery->where('course_id', $course->id));
                    })
                    : $builder->whereHas('lesson', function (Builder $lessonQuery) use ($course): void {
                        $lessonQuery->whereHas('module', fn (Builder $moduleQuery) => $moduleQuery->where('course_id', $course->id));
                    });
                $applied = true;
            }

            if (! $applied) {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function isPublished(): bool
    {
        return in_array($this->status, [null, self::STATUS_PUBLISHED], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_PUBLISHED, null => 'Công khai',
            default => ucfirst((string) $this->status),
        };
    }

    public function durationLabel(): string
    {
        return $this->duration_minutes ? $this->duration_minutes . ' phút' : 'Chưa cấu hình';
    }

    public function totalScoreLabel(): string
    {
        return $this->total_score !== null ? rtrim(rtrim(number_format((float) $this->total_score, 2, '.', ''), '0'), '.') : '10';
    }

    public function targetLabel(): string
    {
        if ($this->classRoom) {
            return $this->classRoom->displayName();
        }

        if ($this->course) {
            return $this->course->title;
        }

        if ($this->subject) {
            return $this->subject->name;
        }

        return 'Chưa gắn lớp/khóa/môn';
    }

    protected static function hasColumn(string $column): bool
    {
        static $columns = [];

        if (! array_key_exists($column, $columns)) {
            $columns[$column] = Schema::hasColumn((new static())->getTable(), $column);
        }

        return $columns[$column];
    }
}

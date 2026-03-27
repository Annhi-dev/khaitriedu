<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Review;
use App\Models\ScheduleChangeRequest;
use App\Models\Subject;
use App\Models\TeacherApplication;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdminReportService
{
    public function build(array $filters): array
    {
        [$startDate, $endDate] = $this->resolveRange($filters);

        return [
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'rangeLabel' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'summary' => $this->summary($startDate, $endDate),
            'quality' => $this->quality($startDate, $endDate),
            'availability' => $this->availability(),
            'activityTrend' => $this->activityTrend($startDate, $endDate),
            'topCourses' => $this->topCourses($startDate, $endDate),
            'topTeachers' => $this->topTeachers($startDate, $endDate),
        ];
    }

    protected function resolveRange(array $filters): array
    {
        $startDate = Carbon::parse($filters['start_date'])->startOfDay();
        $endDate = Carbon::parse($filters['end_date'])->endOfDay();

        return [$startDate, $endDate];
    }

    protected function summary(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'totalStudents' => User::query()->students()->count(),
            'studentsInPeriod' => User::query()->students()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'totalTeachers' => User::query()->teachers()->count(),
            'teachersInPeriod' => User::query()->teachers()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'newEnrollments' => $this->enrollmentPeriodQuery($startDate, $endDate)->count(),
            'totalTeacherApplications' => TeacherApplication::query()->count(),
            'teacherApplicationsInPeriod' => TeacherApplication::query()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'activeClasses' => Course::query()->whereIn('status', Course::schedulingStatuses())->count(),
            'pendingEnrollments' => Enrollment::query()->where('status', Enrollment::STATUS_PENDING)->where('is_submitted', true)->count(),
            'pendingScheduleChanges' => ScheduleChangeRequest::query()->pending()->count(),
            'publicSubjects' => Subject::query()->count(),
        ];
    }

    protected function quality(Carbon $startDate, Carbon $endDate): array
    {
        $gradeQuery = Grade::query()->whereBetween('created_at', [$startDate, $endDate])->whereNotNull('score');
        $totalGrades = (clone $gradeQuery)->count();
        $passedGrades = (clone $gradeQuery)->where('score', '>=', 50)->count();

        $reviewQuery = Review::query()->whereBetween('created_at', [$startDate, $endDate]);
        $courseReviewCount = (clone $reviewQuery)->count();
        $teacherReviewCount = Review::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('course', fn (Builder $query) => $query->whereNotNull('teacher_id'))
            ->count();

        return [
            'averageScore' => $totalGrades > 0 ? round((float) ((clone $gradeQuery)->avg('score')), 1) : null,
            'passRate' => $totalGrades > 0 ? round(($passedGrades / $totalGrades) * 100, 1) : null,
            'gradeCount' => $totalGrades,
            'averageCourseRating' => $courseReviewCount > 0 ? round((float) ((clone $reviewQuery)->avg('rating')), 2) : null,
            'averageTeacherRating' => $teacherReviewCount > 0 ? round((float) (Review::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereHas('course', fn (Builder $query) => $query->whereNotNull('teacher_id'))
                ->avg('rating')), 2) : null,
            'courseReviewCount' => $courseReviewCount,
            'teacherReviewCount' => $teacherReviewCount,
            'reviewedCourseCount' => Review::query()->whereBetween('created_at', [$startDate, $endDate])->distinct('course_id')->count('course_id'),
        ];
    }

    protected function availability(): array
    {
        return [
            'attendance' => [
                'available' => $this->hasAnyTable(['attendance', 'attendances', 'diem_danh', 'attendance_records']),
                'message' => 'He thong hien chua co bang diem danh de tinh ty le chuyen can.',
            ],
            'payments' => [
                'available' => $this->hasAnyTable(['payments', 'payment_transactions', 'thanh_toan', 'hoa_don']),
                'message' => 'He thong hien chua co du lieu thanh toan/doanh thu de tong hop.',
            ],
        ];
    }

    protected function activityTrend(Carbon $startDate, Carbon $endDate): array
    {
        $useDaily = $startDate->diffInDays($endDate) <= 31;
        $period = $useDaily
            ? CarbonPeriod::create($startDate->copy()->startOfDay(), '1 day', $endDate->copy()->startOfDay())
            : CarbonPeriod::create($startDate->copy()->startOfMonth(), '1 month', $endDate->copy()->startOfMonth());

        $keys = [];
        $labels = [];
        foreach ($period as $bucket) {
            $key = $useDaily ? $bucket->format('Y-m-d') : $bucket->format('Y-m');
            $keys[] = $key;
            $labels[$key] = $useDaily ? $bucket->format('d/m') : $bucket->format('m/Y');
        }

        $students = $this->fillTrendSeries($keys, User::query()->students()->whereBetween('created_at', [$startDate, $endDate])->pluck('created_at'), $useDaily);
        $enrollments = $this->fillTrendSeries(
            $keys,
            $this->enrollmentPeriodQuery($startDate, $endDate)
                ->get(['submitted_at', 'created_at'])
                ->map(fn (Enrollment $enrollment) => $enrollment->submitted_at ?? $enrollment->created_at),
            $useDaily
        );
        $applications = $this->fillTrendSeries($keys, TeacherApplication::query()->whereBetween('created_at', [$startDate, $endDate])->pluck('created_at'), $useDaily);
        $reviews = $this->fillTrendSeries($keys, Review::query()->whereBetween('created_at', [$startDate, $endDate])->pluck('created_at'), $useDaily);

        $maxValue = max(array_merge($students, $enrollments, $applications, $reviews, [1]));

        return [
            'labels' => array_map(fn ($key) => $labels[$key], $keys),
            'students' => array_values($students),
            'enrollments' => array_values($enrollments),
            'applications' => array_values($applications),
            'reviews' => array_values($reviews),
            'max' => $maxValue,
            'mode' => $useDaily ? 'day' : 'month',
        ];
    }

    protected function topCourses(Carbon $startDate, Carbon $endDate): Collection
    {
        return Subject::query()
            ->with('category')
            ->withCount([
                'enrollments as enrollments_in_period' => function (Builder $query) use ($startDate, $endDate) {
                    $this->applyEnrollmentPeriod($query, $startDate, $endDate);
                },
            ])
            ->orderByDesc('enrollments_in_period')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->filter(fn ($subject) => $subject->enrollments_in_period > 0)
            ->values();
    }

    protected function topTeachers(Carbon $startDate, Carbon $endDate): Collection
    {
        return User::query()
            ->teachers()
            ->with(['taughtCourses.reviews' => fn ($query) => $query->whereBetween('created_at', [$startDate, $endDate])])
            ->get()
            ->map(function (User $teacher) {
                $reviews = $teacher->taughtCourses->flatMap->reviews;

                return [
                    'teacher' => $teacher,
                    'average_rating' => $reviews->count() > 0 ? round((float) $reviews->avg('rating'), 2) : null,
                    'reviews_count' => $reviews->count(),
                    'courses_count' => $teacher->taughtCourses->filter(fn ($course) => $course->reviews->isNotEmpty())->count(),
                ];
            })
            ->filter(fn (array $row) => $row['reviews_count'] > 0)
            ->sortByDesc(fn (array $row) => ($row['average_rating'] * 1000) + $row['reviews_count'])
            ->take(5)
            ->values();
    }

    protected function fillTrendSeries(array $keys, Collection $timestamps, bool $useDaily): array
    {
        $series = array_fill_keys($keys, 0);

        foreach ($timestamps as $timestamp) {
            if (! $timestamp) {
                continue;
            }

            $bucket = Carbon::parse($timestamp);
            $key = $useDaily ? $bucket->format('Y-m-d') : $bucket->format('Y-m');

            if (array_key_exists($key, $series)) {
                $series[$key]++;
            }
        }

        return $series;
    }

    protected function enrollmentPeriodQuery(Carbon $startDate, Carbon $endDate)
    {
        return Enrollment::query()->where(function (Builder $query) use ($startDate, $endDate) {
            $query->whereBetween('submitted_at', [$startDate, $endDate])
                ->orWhere(function (Builder $builder) use ($startDate, $endDate) {
                    $builder->whereNull('submitted_at')->whereBetween('created_at', [$startDate, $endDate]);
                });
        });
    }

    protected function applyEnrollmentPeriod(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->where(function (Builder $builder) use ($startDate, $endDate) {
            $builder->whereBetween('submitted_at', [$startDate, $endDate])
                ->orWhere(function (Builder $nested) use ($startDate, $endDate) {
                    $nested->whereNull('submitted_at')->whereBetween('created_at', [$startDate, $endDate]);
                });
        });
    }

    protected function hasAnyTable(array $tables): bool
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }
}
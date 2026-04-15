<?php

use App\Models\Course;
use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\CourseCurriculumService;
use App\Services\CourseScheduleSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('curriculum:sync-modules {courseIds?*}', function () {
    $courseIds = collect($this->argument('courseIds') ?? [])
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->values()
        ->all();

    $query = Course::query()
        ->with(['subject.category', 'modules.lessons'])
        ->orderBy('title');

    if ($courseIds !== []) {
        $query->whereIn('id', $courseIds);
    }

    $courses = $query->get();

    if ($courses->isEmpty()) {
        $this->warn('Không tìm thấy khóa học phù hợp.');

        return self::SUCCESS;
    }

    $report = app(CourseCurriculumService::class)->syncCourses($courses);

    $this->info(sprintf(
        'Đã đồng bộ %d khóa, tạo %d module mới, cập nhật %d module, xóa %d module, thêm %d buổi học.',
        $report['courses_processed'],
        $report['modules_created'],
        $report['modules_updated'],
        $report['modules_deleted'],
        $report['lessons_created'],
    ));

    return self::SUCCESS;
})->purpose('Sinh curriculum module chuẩn cho các khóa học');

Artisan::command('schedule:sync-courses {courseIds?*}', function () {
    $courseIds = collect($this->argument('courseIds') ?? [])
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->values()
        ->all();

    $query = Course::query()
        ->with(['subject.category', 'classRooms.schedules'])
        ->orderBy('title');

    if ($courseIds !== []) {
        $query->whereIn('id', $courseIds);
    } else {
        $query->where('title', 'like', 'Khóa 26 - %');
    }

    $courses = $query->get();

    if ($courses->isEmpty()) {
        $this->warn('Không tìm thấy khóa học phù hợp.');

        return self::SUCCESS;
    }

    $report = app(CourseScheduleSyncService::class)->syncCourses($courses);

    $this->info(sprintf(
        'Đã đồng bộ %d khóa, cập nhật %d khóa, tạo %d lớp mới, cập nhật %d lớp, thêm %d buổi học, cập nhật %d buổi học.',
        $report['courses_processed'],
        $report['courses_updated'],
        $report['classrooms_created'],
        $report['classrooms_updated'],
        $report['schedules_created'],
        $report['schedules_updated'],
    ));

    return self::SUCCESS;
})->purpose('Đồng bộ lịch demo cho các khóa học');

Artisan::command('demo:repair-teacher-assignments', function () {
    $teachers = User::query()
        ->teachers()
        ->orderBy('id')
        ->get(['id', 'name']);

    $courses = Course::query()
        ->with(['subject.category', 'classRooms.schedules', 'enrollments'])
        ->orderBy('id')
        ->get();

    if ($courses->isEmpty() || $teachers->isEmpty()) {
        $this->warn('Khong tim thay du lieu demo phu hop.');

        return self::SUCCESS;
    }

    $seeder = new \Database\Seeders\DatabaseSeeder();
    $resolver = new \ReflectionMethod($seeder, 'resolveTeacherIdForCourse');
    $resolver->setAccessible(true);

    $processedCourses = 0;
    $updatedCourses = 0;
    $updatedClassRooms = 0;
    $updatedSchedules = 0;
    $updatedEnrollments = 0;
    $courseIndex = 0;

    DB::transaction(function () use (
        $courses,
        $teachers,
        $seeder,
        $resolver,
        &$processedCourses,
        &$updatedCourses,
        &$updatedClassRooms,
        &$updatedSchedules,
        &$updatedEnrollments,
        &$courseIndex
    ): void {
        foreach ($courses as $course) {
            if (! Str::startsWith(Str::ascii(Str::upper($course->title)), 'KHOA 26 -')) {
                continue;
            }

            $processedCourses++;

            $categoryName = (string) ($course->subject?->category?->name ?? '');
            $subjectName = (string) ($course->subject?->name ?? '');
            $teacherId = $resolver->invoke($seeder, $categoryName, $subjectName, $teachers, $courseIndex);
            $courseIndex++;

            if ($teacherId === null) {
                continue;
            }

            $teacherId = (int) $teacherId;

            if ((int) $course->teacher_id !== $teacherId) {
                $course->forceFill(['teacher_id' => $teacherId])->save();
                $updatedCourses++;
            }

            $activeClassRooms = $course->classRooms()
                ->whereNotIn('status', [ClassRoom::STATUS_CLOSED, ClassRoom::STATUS_COMPLETED])
                ->with('schedules')
                ->get();

            foreach ($activeClassRooms as $classRoom) {
                if ((int) $classRoom->teacher_id !== $teacherId) {
                    $classRoom->forceFill(['teacher_id' => $teacherId])->save();
                    $updatedClassRooms++;
                }

                $updatedSchedules += $classRoom->schedules()
                    ->where('teacher_id', '!=', $teacherId)
                    ->update(['teacher_id' => $teacherId]);
            }

            $updatedEnrollments += $course->enrollments()
                ->whereIn('status', Enrollment::courseAccessStatuses())
                ->where('assigned_teacher_id', '!=', $teacherId)
                ->update(['assigned_teacher_id' => $teacherId]);
        }
    });

    $this->info(sprintf(
        'Da dong bo %d khoa demo. Cap nhat %d khoa, %d lop, %d buoi hoc, %d dang ky.',
        $processedCourses,
        $updatedCourses,
        $updatedClassRooms,
        $updatedSchedules,
        $updatedEnrollments,
    ));

    return self::SUCCESS;
})->purpose('Dong bo giang vien cho cac khoa demo da gan sai');

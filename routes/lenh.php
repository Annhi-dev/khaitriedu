<?php

use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseTimeSlot;
use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\LessonProgress;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\Review;
use App\Models\Room;
use App\Models\ScheduleChangeRequest;
use App\Models\SlotRegistration;
use App\Models\Subject;
use App\Models\TeacherApplication;
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
        $query->where('title', 'like', 'KhaiTriEdu 2026 - %');
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

Artisan::command('demo:seed-report', function () {
    try {
        $line = function (string $label, int $value): void {
            $this->line(sprintf('  %-32s %d', $label . ':', $value));
        };

        $section = function (string $title): void {
            $this->line('');
            $this->info($title);
        };

        $statusCounts = function (string $modelClass): \Illuminate\Support\Collection {
            return $modelClass::query()
                ->select('status', DB::raw('count(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status');
        };

        $printStatusBlock = function (string $title, array $labels, \Illuminate\Support\Collection $counts) use ($section, $line): void {
            $section($title);

            foreach ($labels as $status => $label) {
                $line($label, (int) ($counts->get($status, 0)));
            }
        };

        $this->info('Demo seed report');
        $this->line('Snapshot of the seeded dataset after migrate --seed.');

        $section('Accounts');
        $line('Admins', User::query()->whereHas('role', fn ($query) => $query->where('name', User::ROLE_ADMIN))->count());
        $line('Teachers', User::query()->teachers()->count());
        $line('Students', User::query()->students()->count());
        $line('Active users', User::query()->where('status', User::STATUS_ACTIVE)->count());
        $line('Locked users', User::query()->where('status', User::STATUS_LOCKED)->count());

        $section('Catalog');
        $line('Departments', Department::query()->count());
        $line('Categories', Category::query()->count());
        $line('Subjects', Subject::query()->count());
        $line('Open subjects', Subject::query()->where('status', Subject::STATUS_OPEN)->count());
        $line('Courses', Course::query()->count());
        $line('Demo courses', Course::query()->where('title', 'like', 'KhaiTriEdu 2026 - %')->count());
        $line('Internal courses', Course::query()->where('title', 'like', 'Khóa nội bộ - %')->count());
        $line('Rooms', Room::query()->count());

        $printStatusBlock('Department status', Department::statusOptions(), $statusCounts(Department::class));
        $printStatusBlock('Category status', [
            Category::STATUS_ACTIVE => 'Active',
            Category::STATUS_INACTIVE => 'Inactive',
        ], $statusCounts(Category::class));
        $printStatusBlock('Subject status', [
            Subject::STATUS_DRAFT => 'Draft',
            Subject::STATUS_OPEN => 'Open',
            Subject::STATUS_CLOSED => 'Closed',
            Subject::STATUS_ARCHIVED => 'Archived',
        ], $statusCounts(Subject::class));
        $printStatusBlock('Room status', Room::statusOptions(), $statusCounts(Room::class));
        $printStatusBlock('Course status', [
            Course::STATUS_DRAFT => 'Draft',
            Course::STATUS_PENDING_OPEN => 'Pending open',
            Course::STATUS_SCHEDULED => 'Scheduled',
            Course::STATUS_ACTIVE => 'Active',
            Course::STATUS_COMPLETED => 'Completed',
            Course::STATUS_ARCHIVED => 'Archived',
        ], $statusCounts(Course::class));

        $section('Scheduling');
        $line('Course time slots', CourseTimeSlot::query()->count());
        $line('Open registration slots', CourseTimeSlot::query()->where('status', CourseTimeSlot::STATUS_OPEN_FOR_REGISTRATION)->count());
        $line('Ready to open class slots', CourseTimeSlot::query()->where('status', CourseTimeSlot::STATUS_READY_TO_OPEN_CLASS)->count());
        $line('Class rooms', ClassRoom::query()->count());
        $line('Open class rooms', ClassRoom::query()->where('status', ClassRoom::STATUS_OPEN)->count());
        $line('Full class rooms', ClassRoom::query()->where('status', ClassRoom::STATUS_FULL)->count());
        $line('Closed class rooms', ClassRoom::query()->where('status', ClassRoom::STATUS_CLOSED)->count());
        $line('Completed class rooms', ClassRoom::query()->where('status', ClassRoom::STATUS_COMPLETED)->count());
        $line('Enrollment records', Enrollment::query()->count());
        $line('Slot registrations', SlotRegistration::query()->count());
        $line('Schedule change requests', ScheduleChangeRequest::query()->count());

        $printStatusBlock('Enrollment status', [
            Enrollment::STATUS_PENDING => 'Pending',
            Enrollment::STATUS_APPROVED => 'Approved',
            Enrollment::STATUS_REJECTED => 'Rejected',
            Enrollment::STATUS_ENROLLED => 'Enrolled',
            Enrollment::STATUS_SCHEDULED => 'Scheduled',
            Enrollment::STATUS_ACTIVE => 'Active',
            Enrollment::STATUS_COMPLETED => 'Completed',
        ], $statusCounts(Enrollment::class));
        $printStatusBlock('Slot registration status', SlotRegistration::statusOptions(), $statusCounts(SlotRegistration::class));
        $printStatusBlock('Schedule change status', [
            ScheduleChangeRequest::STATUS_PENDING => 'Pending',
            ScheduleChangeRequest::STATUS_APPROVED => 'Approved',
            ScheduleChangeRequest::STATUS_REJECTED => 'Rejected',
        ], $statusCounts(ScheduleChangeRequest::class));

        $section('Learning Outcomes');
        $line('Attendance records', AttendanceRecord::query()->count());
        $line('Grades', Grade::query()->count());
        $line('Teacher evaluations', \App\Models\TeacherEvaluation::query()->count());
        $line('Quizzes', Quiz::query()->count());
        $line('Questions', Question::query()->count());
        $line('Quiz answers', QuizAnswer::query()->count());
        $line('Lesson progress rows', LessonProgress::query()->count());
        $line('Certificates', Certificate::query()->count());
        $line('Reviews', Review::query()->count());
        $line('Announcements', Announcement::query()->count());
        $line('Notifications', Notification::query()->count());

        $section('Teacher Applications');
        $line('Applications total', TeacherApplication::query()->count());
        $printStatusBlock('Teacher application status', [
            TeacherApplication::STATUS_PENDING => 'Pending',
            TeacherApplication::STATUS_APPROVED => 'Approved',
            TeacherApplication::STATUS_REJECTED => 'Rejected',
            TeacherApplication::STATUS_NEEDS_REVISION => 'Needs revision',
        ], $statusCounts(TeacherApplication::class));

        return self::SUCCESS;
    } catch (\Throwable $e) {
        $this->error('Khong the tao demo seed report: ' . $e->getMessage());

        return self::FAILURE;
    }
})->purpose('Hien thi tong quan du lieu demo da seed');

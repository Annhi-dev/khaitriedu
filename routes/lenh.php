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
use App\Services\AdminScheduleConflictService;
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

Artisan::command('enrollment:audit-class-links {--repair}', function () {
    $repair = (bool) $this->option('repair');

    $enrollments = Enrollment::query()
        ->with([
            'user',
            'course.classRooms.room',
            'course.classRooms.teacher',
            'course.classRooms.schedules',
            'classRoom.room',
            'classRoom.teacher',
            'classRoom.schedules',
        ])
        ->where(function ($query): void {
            $query->where(function ($builder): void {
                $builder->whereNull('lop_hoc_id')
                    ->whereNotNull('course_id');
            })->orWhere(function ($builder): void {
                $builder->whereNotNull('lop_hoc_id')
                    ->whereNull('course_id');
            });
        })
        ->orderBy('id')
        ->get();

    if ($enrollments->isEmpty()) {
        $this->info('Không tìm thấy enrollment nào cần kiểm tra.');

        return self::SUCCESS;
    }

    $fixed = 0;
    $ambiguous = [];

    DB::transaction(function () use ($enrollments, $repair, &$fixed, &$ambiguous): void {
        foreach ($enrollments as $enrollment) {
            $updates = [];

            if ($enrollment->lop_hoc_id !== null && $enrollment->course_id === null && $enrollment->classRoom) {
                $updates['course_id'] = $enrollment->classRoom->course_id;

                if ($enrollment->subject_id === null) {
                    $updates['subject_id'] = $enrollment->classRoom->subject_id;
                }

                if ($enrollment->assigned_teacher_id === null) {
                    $updates['assigned_teacher_id'] = $enrollment->classRoom->teacher_id;
                }

                if (! filled($enrollment->schedule)) {
                    $updates['schedule'] = $enrollment->classRoom->scheduleSummary();
                }
            }

            if ($enrollment->lop_hoc_id === null && $enrollment->course_id !== null) {
                $resolvedClassRoom = $enrollment->historicalClassRoom();

                if ($resolvedClassRoom) {
                    $updates['lop_hoc_id'] = $resolvedClassRoom->id;
                    $updates['course_id'] = $resolvedClassRoom->course_id;
                    $updates['subject_id'] = $resolvedClassRoom->subject_id;
                    $updates['assigned_teacher_id'] = $resolvedClassRoom->teacher_id;

                    if (! filled($enrollment->schedule)) {
                        $updates['schedule'] = $resolvedClassRoom->scheduleSummary();
                    }
                } else {
                    $ambiguous[] = sprintf(
                        '#%d %s | %s | %s',
                        $enrollment->id,
                        $enrollment->user?->name ?? 'Chưa rõ',
                        $enrollment->course?->title ?? 'Chưa rõ khóa',
                        $enrollment->schedule ?? 'Chưa có lịch lưu'
                    );
                }
            }

            if ($updates === []) {
                continue;
            }

            $fixed++;

            if ($repair) {
                $enrollment->forceFill($updates)->save();
            }
        }
    });

    $this->info(sprintf(
        'Đã kiểm tra %d enrollment, có thể xử lý %d bản ghi%s.',
        $enrollments->count(),
        $fixed,
        $repair ? ' và đã cập nhật DB' : ''
    ));

    if ($ambiguous !== []) {
        $this->warn('Một số bản ghi chưa thể suy ra lớp an toàn:');

        foreach ($ambiguous as $line) {
            $this->line('  - ' . $line);
        }
    }

    return self::SUCCESS;
})->purpose('Rà và vá liên kết course/lớp của enrollment cũ');

Artisan::command('enrollment:dedupe-class-enrollments', function () {
    $duplicates = DB::table('dang_ky')
        ->select('user_id', 'lop_hoc_id', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as total'))
        ->whereNotNull('lop_hoc_id')
        ->groupBy('user_id', 'lop_hoc_id')
        ->having('total', '>', 1)
        ->get();

    if ($duplicates->isEmpty()) {
        $this->info('Không có enrollment trùng lớp để xóa.');

        return self::SUCCESS;
    }

    $deleted = 0;

    DB::transaction(function () use ($duplicates, &$deleted): void {
        foreach ($duplicates as $duplicate) {
            $deleted += DB::table('dang_ky')
                ->where('user_id', $duplicate->user_id)
                ->where('lop_hoc_id', $duplicate->lop_hoc_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }
    });

    $this->info(sprintf(
        'Đã xóa %d bản ghi enrollment trùng lớp trong %d nhóm trùng.',
        $deleted,
        $duplicates->count()
    ));

    return self::SUCCESS;
})->purpose('Xoa enrollment bi trung theo hoc vien va lop hoc');

Artisan::command('enrollment:prune-schedule-conflicts {--dry-run}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $conflicts = app(AdminScheduleConflictService::class)->studentConflicts();

    if ($conflicts->isEmpty()) {
        $this->info('Không có xung đột lịch học nào để xử lý.');

        return self::SUCCESS;
    }

    $enrollmentIdsToDelete = collect();
    $groupsProcessed = 0;

    foreach ($conflicts as $group) {
        $groupsProcessed++;
        $nodes = collect();

        foreach ($group['conflicts'] ?? [] as $pair) {
            $firstId = (int) ($pair['first']['enrollment_id'] ?? 0);
            $secondId = (int) ($pair['second']['enrollment_id'] ?? 0);

            if ($firstId > 0 && $secondId > 0) {
                $nodes = $nodes->merge([$firstId, $secondId]);
            }
        }

        $nodes = $nodes->filter()->unique()->sort()->values();

        if ($nodes->count() <= 1) {
            continue;
        }

        $adjacency = [];
        foreach ($nodes as $id) {
            $adjacency[$id] = [];
        }

        foreach ($group['conflicts'] ?? [] as $pair) {
            $firstId = (int) ($pair['first']['enrollment_id'] ?? 0);
            $secondId = (int) ($pair['second']['enrollment_id'] ?? 0);

            if ($firstId <= 0 || $secondId <= 0) {
                continue;
            }

            $adjacency[$firstId][] = $secondId;
            $adjacency[$secondId][] = $firstId;
        }

        $visited = [];

        foreach ($nodes as $startId) {
            if (isset($visited[$startId])) {
                continue;
            }

            $queue = [$startId];
            $component = [];

            while ($queue !== []) {
                $currentId = array_pop($queue);

                if (isset($visited[$currentId])) {
                    continue;
                }

                $visited[$currentId] = true;
                $component[] = $currentId;

                foreach ($adjacency[$currentId] ?? [] as $neighborId) {
                    if (! isset($visited[$neighborId])) {
                        $queue[] = $neighborId;
                    }
                }
            }

            if ($component === []) {
                continue;
            }

            $keepId = max($component);
            $componentDeletes = collect($component)->reject(fn (int $id) => $id === $keepId)->values();
            $enrollmentIdsToDelete = $enrollmentIdsToDelete->merge($componentDeletes);
        }
    }

    $enrollmentIdsToDelete = $enrollmentIdsToDelete->unique()->values();

    if ($enrollmentIdsToDelete->isEmpty()) {
        $this->info('Không có enrollment nào cần xóa sau khi xét xung đột lịch.');

        return self::SUCCESS;
    }

    $count = $enrollmentIdsToDelete->count();

    if ($dryRun) {
        $this->warn(sprintf(
            'Dry run: sẽ xóa %d enrollment gây trùng lịch từ %d nhóm xung đột.',
            $count,
            $groupsProcessed
        ));

        $this->line('Danh sách id: ' . $enrollmentIdsToDelete->implode(', '));

        return self::SUCCESS;
    }

    $deleted = DB::table('dang_ky')
        ->whereIn('id', $enrollmentIdsToDelete->all())
        ->delete();

    $this->info(sprintf(
        'Đã xóa %d enrollment gây trùng lịch từ %d nhóm xung đột.',
        $deleted,
        $groupsProcessed
    ));

    return self::SUCCESS;
})->purpose('Xoa enrollment gay trung lich hoc, giu lai ban moi nhat trong moi cum');

Artisan::command('enrollment:report-weekly-conflicts {studentId?}', function (?string $studentId = null) {
    $students = User::query()
        ->students()
        ->when($studentId !== null, fn ($query) => $query->whereKey((int) $studentId))
        ->orderBy('id')
        ->get();

    if ($students->isEmpty()) {
        $this->info('Không tìm thấy học viên phù hợp.');

        return self::SUCCESS;
    }

    $reportService = app(\App\Services\StudentScheduleService::class);
    $found = false;

    foreach ($students as $student) {
        $weeklyTimetable = $reportService->weeklyTimetable($student);

        if (empty($weeklyTimetable['hasConflicts'])) {
            continue;
        }

        $found = true;
        $this->info(sprintf(
            '#%d %s: %d buổi trùng',
            $student->id,
            $student->displayName(),
            (int) ($weeklyTimetable['conflictCount'] ?? 0)
        ));

        foreach ($weeklyTimetable['conflicts'] ?? [] as $conflict) {
            $this->line(sprintf(
                '  - %s | %s (%s) | %s (%s)',
                $conflict['day_label'] ?? ($conflict['day_of_week'] ?? 'Chưa rõ ngày'),
                $conflict['first_title'] ?? 'Buổi học 1',
                $conflict['first_status'] ?? 'unknown',
                $conflict['second_title'] ?? 'Buổi học 2',
                $conflict['second_status'] ?? 'unknown'
            ));
        }
    }

    if (! $found) {
        $this->info('Không có học viên nào còn lịch bị trùng trong tuần hiện tại.');
    }

    return self::SUCCESS;
})->purpose('Bao cao hoc vien con trung lich trong thoi khoa bieu tuan');

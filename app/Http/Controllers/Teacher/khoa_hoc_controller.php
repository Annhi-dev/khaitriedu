<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function courses()
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $courses = Course::where('teacher_id', $user->id)
            ->with(['subject.category', 'modules'])
            ->withCount([
                'enrollments as active_enrollments_count' => fn ($query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
            ])
            ->get();

        $enrollments = Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->whereIn('status', Enrollment::courseAccessStatuses())
            ->with(['user', 'course.subject', 'classRoom'])
            ->orderByDesc('id')
            ->get();

        Enrollment::syncDisplayStatusesByClass($enrollments);

        return view('giao_vien.khoa_hoc', compact('user', 'courses', 'enrollments'));
    }

    public function showCourse($id)
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $course = Course::where('teacher_id', $user->id)
            ->with([
                'subject.category',
                'modules.lessons',
                'enrollments' => fn ($query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
                'enrollments.user',
                'enrollments.classRoom',
            ])
            ->find($id);

        if (! $course) {
            return redirect()->route('teacher.courses')->with('error', 'Lớp học không tồn tại.');
        }

        Enrollment::syncDisplayStatusesByClass($course->enrollments);

        $gradeMap = Grade::whereIn('enrollment_id', $course->enrollments->pluck('id'))
            ->get()
            ->keyBy(function ($grade) {
                return $grade->enrollment_id . '-' . ($grade->module_id ?? 'summary');
            });

        $lessonToModule = [];
        $lessonIds = [];

        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                $lessonToModule[$lesson->id] = $module->id;
                $lessonIds[] = $lesson->id;
            }
        }

        $lessonIds = array_values(array_unique($lessonIds));
        $studentIds = $course->enrollments->pluck('user_id')->filter()->unique()->values();

        $completedByStudent = [];
        $completedByStudentAndModule = [];

        if ($studentIds->isNotEmpty() && $lessonIds !== []) {
            $progressRows = LessonProgress::query()
                ->whereIn('user_id', $studentIds)
                ->whereIn('lesson_id', $lessonIds)
                ->where('is_completed', true)
                ->get(['user_id', 'lesson_id']);

            foreach ($progressRows as $progress) {
                $moduleId = $lessonToModule[$progress->lesson_id] ?? null;

                if (! $moduleId) {
                    continue;
                }

                $completedByStudent[$progress->user_id] = ($completedByStudent[$progress->user_id] ?? 0) + 1;
                $completedByStudentAndModule[$progress->user_id][$moduleId] = ($completedByStudentAndModule[$progress->user_id][$moduleId] ?? 0) + 1;
            }
        }

        $totalCourseLessons = (int) $course->modules->sum(fn ($module) => $module->plannedSessionCount());
        $studentModuleProgress = [];
        $studentCourseProgress = [];

        foreach ($course->enrollments as $enrollment) {
            $studentId = $enrollment->user_id;

            foreach ($course->modules as $module) {
                $totalLessons = $module->plannedSessionCount();
                $completedLessons = $completedByStudentAndModule[$studentId][$module->id] ?? 0;
                $completedLessons = min($completedLessons, $totalLessons);

                $studentModuleProgress[$enrollment->id][$module->id] = [
                    'completed' => $completedLessons,
                    'total' => $totalLessons,
                    'percent' => $totalLessons > 0
                        ? (int) round(($completedLessons / $totalLessons) * 100)
                        : 0,
                ];
            }

            $completedCourseLessons = min($completedByStudent[$studentId] ?? 0, $totalCourseLessons);

            $studentCourseProgress[$enrollment->id] = [
                'completed' => $completedCourseLessons,
                'total' => $totalCourseLessons,
                'percent' => $totalCourseLessons > 0
                    ? (int) round(($completedCourseLessons / $totalCourseLessons) * 100)
                    : 0,
            ];
        }

        return view('giao_vien.chi_tiet_khoa_hoc', compact(
            'course',
            'user',
            'gradeMap',
            'studentModuleProgress',
            'studentCourseProgress'
        ));
    }

    public function updateGrades(Request $request)
    {
        [$user, $redirect] = $this->requireRole(User::ROLE_TEACHER);

        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'enrollment_id' => 'required|exists:dang_ky,id',
            'module_id' => 'nullable|exists:chuong_hoc,id',
            'score' => 'nullable|numeric|min:0|max:100',
            'grade' => 'nullable|string|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $enrollment = Enrollment::with('course')->find($data['enrollment_id']);

        if (! $enrollment || ! $enrollment->course || $enrollment->course->teacher_id !== $user->id) {
            return back()->with('error', 'Bạn không có quyền nhập điểm cho lớp học này.');
        }

        Grade::updateOrCreate(
            ['enrollment_id' => $data['enrollment_id'], 'module_id' => $data['module_id']],
            array_filter($data, fn ($value) => $value !== null)
        );

        return back()->with('status', 'Điểm đã được cập nhật.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function dashboard()
    {
        [$user, $redirect] = $this->requireRole('giang_vien');

        if ($redirect) {
            return $redirect;
        }

        return redirect()->route('teacher.courses');
    }

    public function courses()
    {
        [$user, $redirect] = $this->requireRole('giang_vien');

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
            ->with(['user', 'course.subject'])
            ->orderByDesc('id')
            ->get();

        return view('teacher.courses', compact('user', 'courses', 'enrollments'));
    }

    public function showCourse($id)
    {
        [$user, $redirect] = $this->requireRole('giang_vien');

        if ($redirect) {
            return $redirect;
        }

        $course = Course::where('teacher_id', $user->id)
            ->with([
                'subject.category',
                'modules',
                'enrollments' => fn ($query) => $query->whereIn('status', Enrollment::courseAccessStatuses()),
                'enrollments.user',
            ])
            ->find($id);

        if (! $course) {
            return redirect()->route('teacher.courses')->with('error', 'Lớp học không tồn tại.');
        }

        $gradeMap = Grade::whereIn('enrollment_id', $course->enrollments->pluck('id'))
            ->get()
            ->keyBy(function ($grade) {
                return $grade->enrollment_id . '-' . ($grade->module_id ?? 'summary');
            });

        return view('teacher.course_show', compact('course', 'user', 'gradeMap'));
    }

    public function updateGrades(Request $request)
    {
        [$user, $redirect] = $this->requireRole('giang_vien');

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
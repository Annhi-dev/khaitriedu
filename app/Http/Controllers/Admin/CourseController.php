<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $courses = Course::with(['subject.category', 'teacher'])->withCount('enrollments')->orderBy('id', 'desc')->get();
        $subjects = Subject::with('category')->orderBy('name')->get();

        return view('admin.courses', compact('courses', 'subjects', 'current'));
    }

    public function show(Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $course->load(['subject.category', 'teacher', 'modules'])->loadCount('enrollments');
        $teachers = User::where('role', User::ROLE_TEACHER)->orderBy('name')->get();
        $subjects = Subject::with('category')->orderBy('name')->get();

        return view('admin.course.show', compact('course', 'teachers', 'subjects', 'current'));
    }

    public function store(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'subject_id' => 'required|exists:mon_hoc,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        Course::create($data);

        return redirect()->route('admin.courses')->with('status', 'Lớp học đã được thêm.');
    }

    public function update(Request $request, Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:mon_hoc,id',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        $course->update($data);

        return redirect()->route('admin.course.show', $course)->with('status', 'Lớp học đã được cập nhật.');
    }

    public function destroy(Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $course->delete();

        return redirect()->route('admin.courses')->with('status', 'Lớp học đã xóa.');
    }

    public function assign(Request $request, Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        $course->update($data);

        return redirect()->route('admin.courses')->with('status', 'Lớp học đã cập nhật giảng viên và lịch.');
    }

    public function storeSubjectCourse(Request $request, Subject $subject)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Course::create([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        return redirect()->route('admin.subjects')->with('status', 'Lớp học đã thêm.');
    }
}

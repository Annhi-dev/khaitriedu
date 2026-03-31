<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $selectedSubject = null;
        $selectedCategory = null;
        $returnToCategoryId = $request->filled('return_to_category_id')
            ? (int) $request->query('return_to_category_id')
            : null;

        if ($request->filled('subject_id')) {
            $selectedSubject = Subject::with('category')->find((int) $request->query('subject_id'));
            $selectedCategory = $selectedSubject?->category;
        }

        if (! $selectedCategory && $returnToCategoryId) {
            $selectedCategory = Category::find($returnToCategoryId);
        }

        $subjectsQuery = Subject::with('category')->orderBy('name');

        if ($selectedCategory) {
            $subjectsQuery->where('category_id', $selectedCategory->id);
        }

        $subjects = $subjectsQuery->get();

        if ($selectedSubject && ! $subjects->contains('id', $selectedSubject->id)) {
            $subjects = $subjects->prepend($selectedSubject);
        }

        $coursesQuery = Course::with(['subject.category', 'teacher'])
            ->withCount('enrollments')
            ->orderBy('title');

        if ($selectedCategory) {
            $coursesQuery->whereHas('subject', fn ($query) => $query->where('category_id', $selectedCategory->id));
        }

        $courses = $coursesQuery->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.courses', compact(
            'courses',
            'subjects',
            'categories',
            'current',
            'selectedSubject',
            'selectedCategory',
            'returnToCategoryId',
        ));
    }

    public function show(Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $course->load(['subject.category', 'teacher', 'modules'])->loadCount('enrollments');
        $teachers = User::teachers()->orderBy('name')->get();
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
            'price' => 'nullable|numeric|min:0',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
            'return_to_category_id' => 'nullable|exists:danh_muc,id',
        ]);

        Course::create([
            'subject_id' => $data['subject_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'teacher_id' => $data['teacher_id'] ?? null,
            'schedule' => $data['schedule'] ?? null,
        ]);

        $subject = Subject::with('category')->find($data['subject_id']);
        $returnToCategoryId = (int) ($data['return_to_category_id'] ?? 0);

        if ($returnToCategoryId > 0 && (int) ($subject?->category_id ?? 0) === $returnToCategoryId) {
            return redirect()->route('admin.categories.show', $returnToCategoryId)->with('status', 'Khóa học đã được thêm vào nhóm học.');
        }

        return redirect()->route('admin.courses')->with('status', 'Khóa học đã được thêm.');
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
            'price' => 'nullable|numeric|min:0',
            'subject_id' => 'required|exists:mon_hoc,id',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        $data['price'] = $data['price'] ?? 0;
        $course->update($data);

        return redirect()->route('admin.course.show', $course)->with('status', 'Khóa học đã được cập nhật.');
    }

    public function destroy(Course $course)
    {
        [$current, $redirect] = $this->requireRole(User::ROLE_ADMIN);
        if ($redirect) {
            return $redirect;
        }

        $course->delete();

        return redirect()->route('admin.courses')->with('status', 'Khóa học đã xóa.');
    }

    public function apiBaseCourse($id)
    {
        // Must allow anonymous or just admin? The requirement did not specify auth, 
        // but it'll be hit by the admin form.
        $subject = \App\Models\Subject::findOrFail($id);
        
        $totalClasses = \App\Models\Course::where('subject_id', $subject->id)->count();

        return response()->json([
            'name' => $subject->name,
            'price' => $subject->price ?? 0,
            'capacity' => 30, // Default capacity
            'total_classes' => $totalClasses
        ]);
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

        return redirect()->route('admin.courses')->with('status', 'Khóa học đã cập nhật giảng viên và lịch.');
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
            'price' => 'nullable|numeric|min:0',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        Course::create([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'teacher_id' => $data['teacher_id'] ?? null,
            'schedule' => $data['schedule'] ?? null,
        ]);

        return back()->with('status', 'Khóa học thực tế đã được thêm vào khóa gốc.');
    }
}
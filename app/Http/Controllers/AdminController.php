<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\TeacherApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        [$user, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $courseCount = Course::count();
        $subjectCount = Subject::count();
        $studentCount = User::where('role', 'hoc_vien')->count();
        $teacherCount = User::where('role', 'giang_vien')->count();
        $newEnrollments = Enrollment::where('status', 'pending')->where('is_submitted', true)->count();
        $pendingTeacherApplications = TeacherApplication::where('status', 'pending')->count();

        return view('dashboard_admin', compact('user', 'courseCount', 'subjectCount', 'studentCount', 'teacherCount', 'newEnrollments', 'pendingTeacherApplications'));
    }

    public function report()
    {
        [$user, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $courseCount = Course::count();
        $subjectCount = Subject::count();
        $studentCount = User::where('role', 'hoc_vien')->count();
        $teacherCount = User::where('role', 'giang_vien')->count();
        $pendingEnrollments = Enrollment::where('status', 'pending')->where('is_submitted', true)->count();

        return view('admin.report', compact('courseCount', 'subjectCount', 'studentCount', 'teacherCount', 'pendingEnrollments', 'user'));
    }

    public function teacherApplications()
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $applications = TeacherApplication::orderBy('created_at', 'desc')->get();

        return view('admin.teacher_applications', compact('applications', 'current'));
    }

    public function showTeacherApplication($id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $application = TeacherApplication::find($id);
        if (! $application) {
            return redirect()->route('admin.teacher-applications')->with('error', 'Hồ sơ không tồn tại.');
        }

        return view('admin.teacher_application_show', compact('application', 'current'));
    }

    public function reviewTeacherApplication(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $application = TeacherApplication::find($id);
        if (! $application) {
            return redirect()->route('admin.teacher-applications')->with('error', 'Hồ sơ không tồn tại.');
        }

        $action = $request->input('action');
        if (! in_array($action, ['approved', 'rejected'])) {
            return redirect()->route('admin.teacher-applications')->with('error', 'Hành động không hợp lệ.');
        }

        $application->status = $action;
        $application->reviewed_at = now();
        $application->reviewed_by = $current->id;
        $application->save();

        if ($action === 'approved' && ! User::where('email', $application->email)->exists()) {
            User::create([
                'name' => $application->name,
                'email' => $application->email,
                'username' => explode('@', $application->email)[0] . '.' . rand(100, 999),
                'password' => Hash::make('12345678'),
                'role' => 'giang_vien',
                'email_verified_at' => now(),
            ]);
        }

        return redirect()->route('admin.teacher-applications')->with('status', 'Cập nhật tình trạng hồ sơ thành công.');
    }

    public function users()
    {
        [$user, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $users = User::orderBy('id', 'desc')->get();

        return view('admin.users', compact('users', 'user'));
    }

    public function showUser($id)
    {
        [$user, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $target = User::find($id);
        if (! $target) {
            return redirect()->route('admin.users')->with('error', 'Người dùng không tồn tại.');
        }

        return view('admin.user.show', compact('target', 'user'));
    }

    public function storeUser(Request $request)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,giang_vien,hoc_vien',
        ]);

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'email_verified_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.users')->with('status', 'Người dùng được thêm thành công.');
    }

    public function updateUser(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $user = User::find($id);
        if (! $user) {
            return back()->with('error', 'Người dùng không tồn tại.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,giang_vien,hoc_vien',
        ]);

        $user->update($data);

        return redirect()->route('admin.users')->with('status', 'Cập nhật người dùng thành công.');
    }

    public function deleteUser($id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        if ($user = User::find($id)) {
            $user->delete();
        }

        return redirect()->route('admin.users')->with('status', 'Người dùng đã được xóa.');
    }

    public function categories()
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $categories = Category::orderBy('order', 'asc')->get();

        return view('admin.categories', compact('categories', 'current'));
    }

    public function storeCategory(Request $request)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:danh_muc,slug',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $categoryData = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'order' => $data['order'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            $categoryData['image_path'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($categoryData);

        return redirect()->route('admin.categories')->with('status', 'Nhóm ngành đã thêm.');
    }

    public function updateCategory(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $category = Category::find($id);
        if (! $category) {
            return back()->with('error', 'Nhóm ngành không tồn tại.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:danh_muc,slug,' . $id,
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $update = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'order' => $data['order'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            $update['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($update);

        return redirect()->route('admin.categories')->with('status', 'Nhóm ngành đã cập nhật.');
    }

    public function deleteCategory($id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        if ($category = Category::find($id)) {
            $category->delete();
        }

        return redirect()->route('admin.categories')->with('status', 'Nhóm ngành đã xóa.');
    }

    public function subjects()
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $subjects = Subject::with('category')->withCount('courses')->orderBy('id', 'desc')->get();
        $categories = Category::orderBy('order', 'asc')->get();

        return view('admin.subjects', compact('subjects', 'categories', 'current'));
    }

    public function showSubject($id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $subject = Subject::with(['category', 'courses.teacher'])->find($id);
        if (! $subject) {
            return redirect()->route('admin.subjects')->with('error', 'Khóa học không tồn tại.');
        }

        $categories = Category::orderBy('order', 'asc')->get();

        return view('admin.subject.show', compact('subject', 'categories', 'current'));
    }

    public function storeSubject(Request $request)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0|max:9999999999',
            'category_id' => 'nullable|exists:danh_muc,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $subjectData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'category_id' => $data['category_id'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $subjectData['image'] = $request->file('image')->store('subjects', 'public');
        }

        Subject::create($subjectData);

        return redirect()->route('admin.subjects')->with('status', 'Khóa học đã thêm.');
    }

    public function updateSubject(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $subject = Subject::find($id);
        if (! $subject) {
            return back()->with('error', 'Khóa học không tồn tại.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0|max:9999999999',
            'category_id' => 'nullable|exists:danh_muc,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $update = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? 0,
            'category_id' => $data['category_id'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $update['image'] = $request->file('image')->store('subjects', 'public');
        }

        $subject->update($update);

        return redirect()->route('admin.subjects')->with('status', 'Khóa học đã cập nhật.');
    }

    public function deleteSubject($id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        if ($subject = Subject::find($id)) {
            Course::where('subject_id', $id)->delete();
            $subject->delete();
        }

        return redirect()->route('admin.subjects')->with('status', 'Khóa học đã xóa.');
    }

    public function storeSubjectCourse(Request $request, $subjectId)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $subject = Subject::find($subjectId);
        if (! $subject) {
            return back()->with('error', 'Khóa học không tồn tại.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Course::create([
            'subject_id' => $subjectId,
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        return redirect()->route('admin.subjects')->with('status', 'Lớp học đã thêm.');
    }

    public function courses()
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $courses = Course::with(['subject.category', 'teacher'])->withCount('enrollments')->orderBy('id', 'desc')->get();
        $subjects = Subject::with('category')->orderBy('name')->get();

        return view('admin.courses', compact('courses', 'subjects', 'current'));
    }

    public function showCourse($id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $course = Course::with(['subject.category', 'teacher', 'modules'])->withCount('enrollments')->find($id);
        if (! $course) {
            return redirect()->route('admin.courses')->with('error', 'Lớp học không tồn tại.');
        }

        $teachers = User::where('role', 'giang_vien')->orderBy('name')->get();
        $subjects = Subject::with('category')->orderBy('name')->get();

        return view('admin.course.show', compact('course', 'teachers', 'subjects', 'current'));
    }

    public function storeCourse(Request $request)
    {
        [$current, $redirect] = $this->requireRole('admin');
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

    public function updateCourse(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $course = Course::find($id);
        if (! $course) {
            return back()->with('error', 'Lớp học không tồn tại.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:mon_hoc,id',
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        $course->update($data);

        return redirect()->route('admin.course.show', $id)->with('status', 'Lớp học đã được cập nhật.');
    }

    public function deleteCourse($id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        if ($course = Course::find($id)) {
            $course->delete();
        }

        return redirect()->route('admin.courses')->with('status', 'Lớp học đã xóa.');
    }

    public function assignCourse(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $course = Course::find($id);
        if (! $course) {
            return back()->with('error', 'Lớp học không tồn tại.');
        }

        $data = $request->validate([
            'teacher_id' => 'nullable|exists:nguoi_dung,id',
            'schedule' => 'nullable|string|max:255',
        ]);

        $course->update($data);

        return redirect()->route('admin.courses')->with('status', 'Lớp học đã cập nhật giảng viên và lịch.');
    }

    public function storeCourseModule(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $course = Course::find($id);
        if (! $course) {
            return back()->with('error', 'Lớp học không tồn tại.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $course->modules()->create([
            'title' => $data['title'],
            'content' => $data['content'],
            'position' => $data['position'] ?? 1,
        ]);

        return redirect()->route('admin.courses')->with('status', 'Module đã thêm.');
    }

    public function enrollments()
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $enrollments = Enrollment::with(['user', 'course.subject.category', 'subject.category', 'assignedTeacher'])->orderBy('id', 'desc')->get();
        $teachers = User::where('role', 'giang_vien')->orderBy('name')->get();
        $courses = Course::with('subject')->orderBy('id', 'desc')->get();

        return view('admin.enrollments', compact('enrollments', 'teachers', 'courses', 'current'));
    }

    public function updateEnrollment(Request $request, $id)
    {
        [$current, $redirect] = $this->requireRole('admin');
        if ($redirect) {
            return $redirect;
        }

        $enrollment = Enrollment::find($id);
        if (! $enrollment) {
            return back()->with('error', 'Yêu cầu không tồn tại.');
        }

        $data = $request->validate([
            'course_id' => 'nullable|exists:khoa_hoc,id',
            'status' => 'required|in:pending,confirmed,rejected',
            'assigned_teacher_id' => 'nullable|exists:nguoi_dung,id',
            'note' => 'nullable|string',
            'schedule' => 'nullable|string|max:255',
        ]);

        if ($data['status'] === 'confirmed' && empty($data['course_id']) && ! $enrollment->course_id) {
            return back()->with('error', 'Cần chọn lớp học trước khi xác nhận đăng ký.');
        }

        $selectedCourse = null;
        if (! empty($data['course_id'])) {
            $selectedCourse = Course::with('teacher')->find($data['course_id']);
            $enrollment->course_id = $selectedCourse?->id;
            $enrollment->subject_id = $selectedCourse?->subject_id;
        } elseif ($enrollment->course_id) {
            $selectedCourse = Course::with('teacher')->find($enrollment->course_id);
        }

        $assignedTeacherId = $data['assigned_teacher_id'] ?? null;
        if (! $assignedTeacherId && $selectedCourse?->teacher_id) {
            $assignedTeacherId = $selectedCourse->teacher_id;
        }

        $finalSchedule = $data['schedule'] ?? null;
        if (! $finalSchedule && $selectedCourse?->schedule) {
            $finalSchedule = $selectedCourse->schedule;
        }

        $enrollment->status = $data['status'];
        $enrollment->assigned_teacher_id = $assignedTeacherId;
        $enrollment->schedule = $finalSchedule;
        $enrollment->note = $data['status'] === 'rejected' ? ($data['note'] ?? null) : null;
        $enrollment->save();

        return redirect()->route('admin.enrollments')->with('status', 'Cập nhật đăng ký thành công.');
    }
}
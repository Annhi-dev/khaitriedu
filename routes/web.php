<?php

use App\Models\User;
use App\Helpers\OtpHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = User::find(session('user_id'));
    if ($user) {
        return redirect()->route('dashboard');
    }
    $studentCount = User::where('role', 'hoc_vien')->count();
    $courseCount = App\Models\Course::count();
    $teacherCount = User::where('role', 'giang_vien')->count();
    $teachers = User::where('role', 'giang_vien')->limit(6)->get();
    $courses = App\Models\Course::with('subject', 'teacher')
        ->orderBy('id', 'desc')
        ->limit(3)
        ->get();
    return view('home', compact('studentCount', 'courseCount', 'teacherCount', 'courses', 'teachers'));})->name('home');

// Trang Khóa học
Route::get('/courses', function () {
    $courses = App\Models\Course::with('subject', 'teacher')->orderBy('id', 'desc')->paginate(12);
    return view('pages.courses', compact('courses'));
})->name('courses.index');

// Trang Giới thiệu
Route::get('/about', function () {
    $studentCount = User::where('role', 'hoc_vien')->count();
    $courseCount = App\Models\Course::count();
    $teacherCount = User::where('role', 'giang_vien')->count();
    return view('pages.about', compact('studentCount', 'courseCount', 'teacherCount'));
})->name('about');

// Trang Liên hệ
Route::get('/contact', function () {return view('pages.contact');})->name('contact');

Route::post('/contact', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|min:10',
    ]);
    // Lưu vào database hoặc gửi email
    Mail::raw("Từ: {$request->name} ({$request->email})\n\n{$request->message}", function($message) use ($request) {$message->to('admin@khaitriedu.com')->subject("Liên hệ: {$request->subject}");});
    return back()->with('status', 'Tin nhắn đã được gửi thành công. Chúng tôi sẽ liên hệ lại trong sớm nhất.');
})->name('contact.post');

// Trang Blog
Route::get('/blog', function () {
    $posts = \App\Models\Announcement::where('status', 'published')
        ->orderByDesc('published_at')
        ->limit(6)
        ->get();
    return view('pages.blog', compact('posts'));
})->name('blog');

// Trang Giảng viên
Route::get('/teachers', function () {
    $teachers = \App\Models\User::where('role', 'giang_vien')->get();
    return view('pages.teachers', compact('teachers'));
})->name('teachers');

// Trang Tuyển dụng
Route::get('/careers', function () {return view('pages.careers');})->name('careers');

// Trang Trung tâm trợ giúp
Route::get('/help', function () {return view('pages.help');})->name('help');

// Trang Điều khoản dịch vụ
Route::get('/terms', function () {return view('pages.terms');})->name('terms');

// Trang Chính sách bảo mật
Route::get('/privacy', function () {return view('pages.privacy');})->name('privacy');

Route::get('/login', function () {return view('auth.login');})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate(['login' => 'required|string', 'password' => 'required']);
    $user = User::where('email', $request->login)
        ->orWhere('username', $request->login)
        ->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->with('error', 'Tên đăng nhập/email hoặc mật khẩu không đúng.');
    }
    session(['user_id' => $user->id]);
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($user->role === 'giang_vien') {
        return redirect()->route('teacher.dashboard');
    }
    return redirect()->route('dashboard');
})->name('login.post');

Route::get('/verify-email', function (Request $request) {return view('auth.verify_email');})->name('verify.email');

Route::post('/verify-email', function (Request $request) {
    $request->validate(['email' => 'required|email', 'code' => 'required|string']);
    $row = DB::table('otp_codes')
        ->where('email', $request->email)
        ->where('type', 'register')
        ->where('code', $request->code)
        ->where('expires_at', '>=', Carbon::now())
        ->first();
    if (!$row) {
        return back()->with('error', 'Mã OTP không đúng hoặc đã hết hạn.');
    }
    DB::table('otp_codes')->where('id', $row->id)->delete();
    $pending = session('pending_user');
    if (!$pending || $pending['email'] !== $request->email) {
        return redirect()->route('register')->with('error', 'Dữ liệu đăng ký không tồn tại hoặc đã hết hạn. Vui lòng đăng ký lại.');
    }
    if (User::where('username', $pending['username'])->orWhere('email', $pending['email'])->exists()) {
        session()->forget('pending_user');
        return redirect()->route('login')->with('status', 'Tài khoản đã tồn tại. Vui lòng đăng nhập.');
    }
    User::create([
        'username' => $pending['username'],
        'name' => $pending['name'],
        'email' => $pending['email'],
        'phone' => $pending['phone'],
        'password' => Hash::make($pending['password']),
        'role' => $pending['role'],
        'email_verified_at' => Carbon::now(),
    ]);
    session()->forget('pending_user');
    return redirect()->route('login')->with('status', 'Xác nhận OTP thành công. Tài khoản đã được kích hoạt. Vui lòng đăng nhập.');
})->name('verify.email.post');

Route::post('/verify-email/resend', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    $pending = session('pending_user');
    if (!$pending || $pending['email'] !== $request->email) {
        return response()->json(['error' => 'Dữ liệu đăng ký không tồn tại hoặc đã hết hạn.'], 400);
    }
    OtpHelper::sendOtp($request->email, 'register');
    return response()->json(['message' => 'Mã OTP mới đã được gửi đến email của bạn.']);
})->name('verify.email.resend');

Route::get('/register', function () {return view('auth.register');})->name('register');

Route::get('/forgot-password', function () {return view('auth.password');})->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);
    $user = User::where('email', $request->email)->first();
    if (!$user || !$user->email_verified_at) {
        return back()->with('status', 'Nếu email tồn tại và đã được xác minh, chúng tôi đã gửi mã OTP.');
    }
    OtpHelper::sendOtp($request->email, 'password_reset');
    return redirect()->route('forgot.verify', ['email' => $request->email])->with('status', 'Mã OTP đã gửi đến email.');
})->name('password.email');

Route::get('/forgot-password/verify', function () {return view('auth.forgot_verify');})->name('forgot.verify');

Route::post('/forgot-password/verify', function (Request $request) {
    $request->validate(['email' => 'required|email', 'code' => 'required|string']);
    $row = DB::table('otp_codes')
        ->where('email', $request->email)
        ->where('type', 'password_reset')
        ->where('code', $request->code)
        ->where('expires_at', '>=', Carbon::now())
        ->first();
    if (!$row) {
        return back()->with('error', 'Mã OTP không đúng hoặc đã hết hạn.');
    }
    DB::table('otp_codes')->where('id', $row->id)->delete();
    return redirect()->route('forgot.reset', ['email' => $request->email])->with('status', 'Mã OTP hợp lệ. Nhập mật khẩu mới.');
})->name('forgot.verify.post');

Route::get('/forgot-password/reset', function (Request $request) {return view('auth.reset_password');})->name('forgot.reset');

Route::post('/forgot-password/reset', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6|confirmed',
    ]);
    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return redirect()->route('forgot.reset', ['email' => $request->email])->with('status', 'Email không tồn tại.');
    }
    $user->password = Hash::make($request->password);
    $user->save();
    return redirect()->route('login')->with('status', 'Mật khẩu đã đặt lại thành công. Đăng nhập lại.');
})->name('forgot.reset.post');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'username' => 'required|string|max:50',
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|min:6',
        'role' => 'required|in:admin,giang_vien,hoc_vien',
    ]);
    if (User::where('username', $data['username'])->exists() || User::where('email', $data['email'])->exists()) {
        return back()->withErrors(['email' => 'Tên đăng nhập hoặc email đã tồn tại.'])->withInput();
    }
    $current = User::find(session('user_id'));
    if ($current && $current->role === 'admin') {
        User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'email_verified_at' => Carbon::now(),
        ]);
        return redirect()->route('admin.users')->with('status', 'Người dùng đã được tạo và kích hoạt ngay lập tức.');
    }
    session(['pending_user' => [
        'username' => $data['username'],
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'password' => $data['password'],
        'role' => $data['role'],
    ]]);
    OtpHelper::sendOtp($data['email'], 'register');
    return redirect()->route('verify.email', ['email' => $data['email']])->with('status', 'Mã xác nhận đã gửi đến email.');
})->name('register.post');

Route::get('/dashboard', function () {
    $user = User::find(session('user_id'));
    if (!$user) {
        return redirect()->route('login');
    }
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($user->role === 'giang_vien') {
        return redirect()->route('teacher.dashboard');
    }
    return view('dashboard', compact('user'));
})->name('dashboard');

Route::get('/admin', function () {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'admin') {
        return redirect()->route('login');
    }
    $courseCount = App\Models\Course::count();
    $subjectCount = App\Models\Subject::count();
    $studentCount = App\Models\User::where('role','hoc_vien')->count();
    $teacherCount = App\Models\User::where('role','giang_vien')->count();
    $newEnrollments = App\Models\Enrollment::where('status','pending')->where('is_submitted', true)->count();
    $pendingTeacherApplications = App\Models\TeacherApplication::where('status', 'pending')->count();
    return view('dashboard_admin', compact('user', 'courseCount', 'subjectCount', 'studentCount', 'teacherCount', 'newEnrollments', 'pendingTeacherApplications'));
})->name('admin.dashboard');

Route::get('/admin/report', function () {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'admin') {
        return redirect()->route('login');
    }
    $courseCount = App\Models\Course::count();
    $subjectCount = App\Models\Subject::count();
    $studentCount = App\Models\User::where('role','hoc_vien')->count();
    $teacherCount = App\Models\User::where('role','giang_vien')->count();
    $pendingEnrollments = App\Models\Enrollment::where('status','pending')->count();
    return view('admin.report', compact('courseCount', 'subjectCount', 'studentCount', 'teacherCount', 'pendingEnrollments', 'user'));
})->name('admin.report');

Route::get('/apply-teacher', function () {return view('pages.apply-teacher');})->name('apply-teacher');

Route::post('/apply-teacher', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:30',
        'experience' => 'nullable|string|max:2000',
        'message' => 'required|string|max:2000',
    ]);

    App\Models\TeacherApplication::create($data);

    return redirect()->route('apply-teacher')->with('status', 'Đã gửi hồ sơ ứng tuyển. Admin sẽ phản hồi sớm.');
})->name('apply-teacher.post');

Route::get('/admin/teacher-applications', function () {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') {
        return redirect()->route('login');
    }

    $applications = App\Models\TeacherApplication::orderBy('created_at', 'desc')->get();
    return view('admin.teacher_applications', compact('applications', 'current'));
})->name('admin.teacher-applications');

Route::get('/admin/teacher-applications/{id}', function ($id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') {
        return redirect()->route('login');
    }

    $application = App\Models\TeacherApplication::find($id);
    if (!$application) {
        return redirect()->route('admin.teacher-applications')->with('error', 'Hồ sơ không tồn tại.');
    }

    return view('admin.teacher_application_show', compact('application', 'current'));
})->name('admin.teacher-applications.show');

Route::post('/admin/teacher-applications/{id}/review', function (Request $request, $id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') {
        return redirect()->route('login');
    }

    $app = App\Models\TeacherApplication::find($id);
    if (!$app) {
        return redirect()->route('admin.teacher-applications')->with('error', 'Hồ sơ không tồn tại.');
    }

    $action = $request->input('action');
    if (!in_array($action, ['approved', 'rejected'])) {
        return redirect()->route('admin.teacher-applications')->with('error', 'Hành động không hợp lệ.');
    }

    $app->status = $action;
    $app->reviewed_at = now();
    $app->reviewed_by = $current->id;
    $app->save();

    if ($action === 'approved') {
        // tự động thêm user giảng viên (nếu email chưa tồn tại)
        if (!User::where('email', $app->email)->exists()) {
            User::create([
                'name' => $app->name,
                'email' => $app->email,
                'username' => explode('@', $app->email)[0] . '.' . rand(100,999),
                'password' => Hash::make('12345678'),
                'role' => 'giang_vien',
                'email_verified_at' => now(),
            ]);
        }
    }

    return redirect()->route('admin.teacher-applications')->with('status', 'Cập nhật tình trạng hồ sơ thành công.');
})->name('admin.teacher-applications.review');

Route::get('/admin/users', function () {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'admin') {
        return redirect()->route('login');
    }
    $users = User::orderBy('id', 'desc')->get();
    return view('admin.users', compact('users', 'user'));
})->name('admin.users');

Route::get('/admin/users/{id}', function ($id) {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'admin') {
        return redirect()->route('login');
    }
    $target = User::find($id);
    if (!$target) {
        return redirect()->route('admin.users')->with('error', 'Người dùng không tồn tại.');
    }
    return view('admin.user.show', compact('target', 'user'));
})->name('admin.user.show');

Route::post('/admin/users', function (Request $request) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
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
})->name('admin.users.create');

Route::post('/admin/users/{id}/update', function (Request $request, $id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $user = User::find($id);
    if (!$user) return back()->with('error', 'Người dùng không tồn tại.');
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'role' => 'required|in:admin,giang_vien,hoc_vien',
    ]);
    $user->update($data);
    return redirect()->route('admin.users')->with('status', 'Cập nhật người dùng thành công.');
})->name('admin.users.update');

Route::post('/admin/users/{id}/delete', function ($id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $user = User::find($id);
    if ($user) $user->delete();
    return redirect()->route('admin.users')->with('status', 'Người dùng đã được xóa.');
})->name('admin.users.delete');

Route::get('/admin/subjects', function () {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $subjects = App\Models\Subject::with('courses')->orderBy('id', 'desc')->get();
    return view('admin.subjects', compact('subjects', 'current'));
})->name('admin.subjects');

Route::get('/admin/subjects/{id}', function ($id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $subject = App\Models\Subject::with('courses')->find($id);
    if (!$subject) return redirect()->route('admin.subjects')->with('error', 'Môn học không tồn tại.');
    return view('admin.subject.show', compact('subject', 'current'));
})->name('admin.subject.show');

Route::post('/admin/subjects', function (Request $request) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric|min:0|max:9999999999',
        'image' => 'nullable|image|max:2048',
    ]);
    $subjectData = ['name' => $data['name'], 'description' => $data['description'] ?? null, 'price' => $data['price'] ?? 0];
    if ($request->hasFile('image')) {
        $subjectData['image'] = $request->file('image')->store('subjects', 'public');
    }
    App\Models\Subject::create($subjectData);
    return redirect()->route('admin.subjects')->with('status', 'Môn học đã thêm.');
})->name('admin.subjects.create');

Route::post('/admin/subjects/{id}/update', function (Request $request, $id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $subject = App\Models\Subject::find($id);
    if (!$subject) return back()->with('error', 'Môn học không tồn tại.');
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric|min:0|max:9999999999',
        'image' => 'nullable|image|max:2048',
    ]);
    $update = ['name' => $data['name'], 'description' => $data['description'] ?? null, 'price' => $data['price'] ?? 0];
    if ($request->hasFile('image')) {
        $update['image'] = $request->file('image')->store('subjects', 'public');
    }
    $subject->update($update);
    return redirect()->route('admin.subjects')->with('status', 'Môn học đã cập nhật.');
})->name('admin.subjects.update');

Route::post('/admin/subjects/{id}/delete', function ($id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $subject = App\Models\Subject::find($id);
    if ($subject) {
        App\Models\Course::where('subject_id', $id)->delete();
        $subject->delete();
    }
    return redirect()->route('admin.subjects')->with('status', 'Môn học đã xóa.');
})->name('admin.subjects.delete');

Route::post('/admin/subjects/{subject_id}/courses', function (Request $request, $subject_id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $subject = App\Models\Subject::find($subject_id);
    if (!$subject) return back()->with('error', 'Môn học không tồn tại.');
    $data = $request->validate(['title' => 'required|string|max:255', 'description' => 'nullable|string']);
    App\Models\Course::create(['subject_id' => $subject_id, 'title' => $data['title'], 'description' => $data['description']]);
    return redirect()->route('admin.subjects')->with('status', 'Khóa học đã thêm.');
})->name('admin.subject.courses.create');

Route::post('/admin/courses/{id}/update', function (Request $request, $id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $course = App\Models\Course::find($id);
    if (!$course) return back()->with('error', 'Khóa học không tồn tại.');
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'subject_id' => 'required|exists:subjects,id',
        'teacher_id' => 'nullable|exists:users,id',
        'schedule' => 'nullable|string|max:255',
    ]);
    $course->update($data);
    return redirect()->route('admin.course.show', $id)->with('status', 'Khóa học đã cập nhật.');
})->name('admin.courses.update');

Route::post('/admin/courses/{id}/delete', function ($id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $course = App\Models\Course::find($id);
    if ($course) $course->delete();
    return redirect()->route('admin.courses')->with('status', 'Khóa học đã xóa.');
})->name('admin.courses.delete');

Route::get('/admin/courses', function () {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $courses = App\Models\Course::with('subject', 'teacher')->orderBy('id', 'desc')->get();
    $subjects = App\Models\Subject::all();
    $teachers = User::where('role', 'giang_vien')->get();
    return view('admin.courses', compact('courses', 'subjects', 'teachers', 'current'));
})->name('admin.courses');

Route::get('/admin/courses/{id}', function ($id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $course = App\Models\Course::with('subject', 'teacher', 'modules')->find($id);
    if (!$course) return redirect()->route('admin.courses')->with('error', 'Khóa học không tồn tại.');
    $teachers = User::where('role', 'giang_vien')->get();
    $subjects = App\Models\Subject::all();
    return view('admin.course.show', compact('course', 'teachers', 'subjects', 'current'));
})->name('admin.course.show');

Route::post('/admin/courses', function (Request $request) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $data = $request->validate([
        'subject_id' => 'required|exists:subjects,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
    ]);
    App\Models\Course::create($data);
    return redirect()->route('admin.courses')->with('status', 'Khóa học đã thêm.');
})->name('admin.courses.create');

Route::post('/admin/courses/{id}/assign', function (Request $request, $id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $course = App\Models\Course::find($id);
    if (!$course) return back()->with('error', 'Khóa học không tồn tại.');
    $data = $request->validate([
        'teacher_id' => 'nullable|exists:users,id',
        'schedule' => 'nullable|string|max:255',
    ]);
    $course->update($data);
    return redirect()->route('admin.courses')->with('status', 'Khóa học đã cập nhật giảng viên/lịch.');
})->name('admin.courses.assign');

Route::post('/admin/courses/{id}/modules', function (Request $request, $id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $course = App\Models\Course::find($id);
    if (!$course) return back()->with('error', 'Khóa học không tồn tại.');
    $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'nullable|string', 'position' => 'nullable|integer']);
    $course->modules()->create(['title' => $data['title'], 'content' => $data['content'], 'position' => $data['position'] ?? 1]);
    return redirect()->route('admin.courses')->with('status', 'Module đã thêm.');
})->name('admin.courses.modules.create');

Route::get('/admin/enrollments', function () {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $enrollments = App\Models\Enrollment::with('user', 'course')->orderBy('id', 'desc')->get();
    $teachers = User::where('role', 'giang_vien')->get();
    return view('admin.enrollments', compact('enrollments', 'teachers', 'current')); 
})->name('admin.enrollments');

Route::post('/admin/enrollments/{id}/update', function (Request $request, $id) {
    $current = User::find(session('user_id'));
    if (!$current || $current->role !== 'admin') return redirect()->route('login');
    $enrollment = App\Models\Enrollment::find($id);
    if (!$enrollment) return back()->with('error', 'Yêu cầu không tồn tại.');
    $data = $request->validate(['status' => 'required|in:pending,confirmed,rejected', 'assigned_teacher_id' => 'nullable|exists:users,id', 'note' => 'nullable|string', 'schedule' => 'nullable|string|max:255']);
    $enrollment->update($data);
    return redirect()->route('admin.enrollments')->with('status', 'Cập nhật đăng ký thành công.');
})->name('admin.enrollments.update');

Route::get('/courses', function () {
    $user = User::find(session('user_id'));
    $courses = App\Models\Course::with('subject', 'teacher')->orderBy('id', 'desc')->get();
    return view('courses.index', compact('courses', 'user'));
})->name('courses.index');

Route::get('/courses/{id}', function ($id) {
    $user = User::find(session('user_id'));
    $course = App\Models\Course::with('subject', 'modules', 'teacher')->find($id);
    if (!$course) {
        return redirect()->route('courses.index')->with('error', 'Khóa học không tồn tại.');
    }
    return view('courses.show', compact('course', 'user'));
})->name('courses.show');

Route::get('/courses/{course}/modules/{module}/lessons/{lesson}', function ($course, $module, $lesson) {
    $user = User::find(session('user_id'));
    $course = App\Models\Course::with('modules.lessons')->find($course);
    $module = App\Models\Module::with('lessons')->where('course_id', $course->id)->find($module);
    $lesson = App\Models\Lesson::with('quiz')->where('module_id', $module->id)->find($lesson);
    if (!$course || !$module || !$lesson) {
        return redirect()->route('courses.index')->with('error', 'Bài học không tồn tại.');
    }

    // Tự động đánh dấu tiến độ
    if ($user && $user->role === 'hoc_vien') {
        App\Models\LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['started_at' => now(), 'is_completed' => true, 'completed_at' => now(), 'time_spent' => 300]
        );
    }

    return view('courses.lesson', compact('course', 'module', 'lesson', 'user'));
})->name('courses.lesson.show');

Route::get('/courses/{course}/quizzes/{quiz}', function ($course, $quiz) {
    $user = User::find(session('user_id'));
    $course = App\Models\Course::find($course);
    $quiz = App\Models\Quiz::with('questions.options')->find($quiz);
    if (!$course || !$quiz) {
        return redirect()->route('courses.index')->with('error', 'Quiz không tồn tại.');
    }
    return view('courses.quiz', compact('course','quiz','user'));
})->name('courses.quiz.show');

Route::post('/courses/{course}/quizzes/{quiz}/submit', function (Request $request, $course, $quiz) {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'hoc_vien') {
        return redirect()->route('login')->with('error', 'Vui lòng đăng nhập học viên.');
    }
    $course = App\Models\Course::find($course);
    $quiz = App\Models\Quiz::with('questions.options')->find($quiz);
    if (!$course || !$quiz) {
        return redirect()->route('courses.index')->with('error', 'Quiz không tồn tại.');
    }

    $answers = $request->input('answers', []);
    $totalPoints = 0;
    $earnedPoints = 0;

    foreach ($quiz->questions as $question) {
        $totalPoints += $question->points;
        $selected = $answers[$question->id] ?? null;

        if ($question->type === 'short_answer') {
            $isCorrect = false;
        } else {
            $option = App\Models\Option::find($selected);
            $isCorrect = $option ? (bool)$option->is_correct : false;
            if ($isCorrect) {
                $earnedPoints += $question->points;
            }
        }

        App\Models\QuizAnswer::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'option_id' => $selected,
            'answer_text' => is_array($selected) ? json_encode($selected) : $selected,
            'is_correct' => $isCorrect,
            'attempt' => App\Models\QuizAnswer::where('user_id', $user->id)->where('quiz_id', $quiz->id)->count() + 1,
        ]);
    }

    $score = $totalPoints ? round($earnedPoints / $totalPoints * 100, 2) : 0;
    $passed = $score >= ($quiz->passing_score ?: 70);

    if ($passed) {
        App\Models\Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_number' => 'KT'.time().rand(100,999),
            'score' => $score,
            'issued_at' => now(),
            'status' => 'issued',
        ]);
    }

    return redirect()->route('courses.show', $course->id)->with('status', "Quiz hoàn thành: $score%. " . ($passed ? 'Đạt chứng chỉ.' : 'Không đạt.'));
})->name('courses.quiz.submit');

Route::get('/certificates', function () {
    $user = User::find(session('user_id'));
    if (!$user) return redirect()->route('login');
    $certificates = App\Models\Certificate::where('user_id', $user->id)->with('course')->orderBy('issued_at', 'desc')->get();
    return view('certificates.index', compact('user', 'certificates'));
})->name('certificates.index');

Route::get('/certificates/{id}', function ($id) {
    $user = User::find(session('user_id'));
    $cert = App\Models\Certificate::with('course')->find($id);
    if (!$user || !$cert || $cert->user_id !== $user->id) return redirect()->route('certificates.index')->with('error', 'Chứng chỉ không tồn tại.');
    return view('certificates.show', compact('cert', 'user'));
})->name('certificates.show');

Route::post('/courses/{id}/enroll', function (Request $request, $id) {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'hoc_vien') {
        return redirect()->route('login')->with('error', 'Vui lòng đăng nhập bằng tài khoản học viên.');
    }
    $course = App\Models\Course::find($id);
    if (!$course) return back()->with('error', 'Khóa học không tồn tại.');
    
    $data = $request->validate([
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'preferred_days' => 'required|array|min:1',
        'preferred_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
    ]);
    
    // Kiểm tra xem học viên đã gửi yêu cầu cho khóa học này chưa
    $existing = App\Models\Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();
    
    if ($existing) {
        // Nếu đã gửi, khi nào có yêu cầu mới thì cập nhật
        $isResubmit = $existing->status === 'rejected';
        
        $existing->update([
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'preferred_days' => json_encode($data['preferred_days']),
            'is_submitted' => true,
            'submitted_at' => Carbon::now(),
            'status' => $isResubmit ? 'pending' : $existing->status, // Reset thành pending nếu bị từ chối
            'note' => null, // Clear lý do từ chối khi gửi lại
        ]);
        
        $message = $isResubmit 
            ? 'Yêu cầu đã được gửi lại. Admin sẽ xem xét lại yêu cầu của bạn.'
            : 'Cập nhật yêu cầu thành công. Đợi admin xác nhận.';
        
        return back()->with('status', $message);
    }
    
    // Tạo enrollment mới
    App\Models\Enrollment::create([
        'user_id' => $user->id,
        'course_id' => $course->id,
        'start_time' => $data['start_time'],
        'end_time' => $data['end_time'],
        'preferred_days' => json_encode($data['preferred_days']),
        'is_submitted' => true,
        'submitted_at' => Carbon::now(),
        'status' => 'pending'
    ]);
    return back()->with('status', 'Đăng ký đã gửi. Admin sẽ xác nhận và sắp xếp giảng viên.');
})->name('courses.enroll');

Route::get('/student/schedule', function () {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'hoc_vien') {
        return redirect()->route('login');
    }
    $enrollments = App\Models\Enrollment::where('user_id', $user->id)
        ->where('status', 'confirmed')
        ->with('course', 'assignedTeacher')
        ->get();
    return view('student.schedule', compact('user', 'enrollments'));
})->name('student.schedule');

Route::get('/student/grades', function () {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'hoc_vien') {
        return redirect()->route('login');
    }
    $grades = App\Models\Grade::whereHas('enrollment', function($q) use ($user) {
        $q->where('user_id', $user->id);
    })->with('enrollment.course', 'module')->get();
    return view('student.grades', compact('user', 'grades'));
})->name('student.grades');

Route::post('/courses/{id}/review', function (Request $request, $id) {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'hoc_vien') {
        return redirect()->route('login');
    }
    $enrollment = App\Models\Enrollment::where('user_id', $user->id)
        ->where('course_id', $id)
        ->where('status', 'confirmed')
        ->first();
    if (!$enrollment) {
        return back()->with('error', 'Bạn chưa hoàn thành khóa học này.');
    }
    $data = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:1000'
    ]);
    App\Models\Review::updateOrCreate(
        ['user_id' => $user->id, 'course_id' => $id],
        $data
    );
    return back()->with('status', 'Đánh giá đã được gửi.');
})->name('courses.review');

Route::get('/teacher', function () {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'giang_vien') {
        return redirect()->route('login');
    }
    return redirect()->route('teacher.courses');
})->name('teacher.dashboard');

Route::get('/teacher/courses', function () {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'giang_vien') return redirect()->route('login');
    $courses = App\Models\Course::where('teacher_id', $user->id)->with('modules')->get();
    $enrollments = App\Models\Enrollment::whereIn('course_id', $courses->pluck('id'))->with('user', 'course')->get();
    return view('teacher.courses', compact('user', 'courses', 'enrollments'));
})->name('teacher.courses');

Route::get('/teacher/courses/{id}', function ($id) {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'giang_vien') return redirect()->route('login');
    $course = App\Models\Course::where('teacher_id', $user->id)->with('modules', 'enrollments.user')->find($id);
    if (!$course) return redirect()->route('teacher.courses')->with('error', 'Khóa học không tồn tại.');
    return view('teacher.course_show', compact('course', 'user'));
})->name('teacher.course.show');

Route::post('/teacher/grades', function (Request $request) {
    $user = User::find(session('user_id'));
    if (!$user || $user->role !== 'giang_vien') return redirect()->route('login');
    $data = $request->validate([
        'enrollment_id' => 'required|exists:enrollments,id',
        'module_id' => 'nullable|exists:modules,id',
        'score' => 'nullable|numeric|min:0|max:100',
        'grade' => 'nullable|string|max:5',
        'feedback' => 'nullable|string|max:1000'
    ]);
    // Verify teacher owns the course
    $enrollment = App\Models\Enrollment::with('course')->find($data['enrollment_id']);
    if ($enrollment->course->teacher_id !== $user->id) {
        return back()->with('error', 'Bạn không có quyền nhập điểm cho khóa học này.');
    }
    App\Models\Grade::updateOrCreate(
        ['enrollment_id' => $data['enrollment_id'], 'module_id' => $data['module_id']],
        array_filter($data, fn($v) => $v !== null)
    );
    return back()->with('status', 'Điểm đã được cập nhật.');
})->name('teacher.grades.update');

Route::get('/logout', function () {session()->forget(['user_id']);return redirect()->route('home');})->name('logout');

Route::fallback(function () {return redirect()->route('home');});

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherScheduleChangeRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');

Route::prefix('courses')->name('courses.')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('index');
    Route::get('/{id}', [CourseController::class, 'show'])->name('show');
    Route::get('/{course}/modules/{module}/lessons/{lesson}', [CourseController::class, 'showLesson'])->name('lesson.show');
    Route::get('/{course}/quizzes/{quiz}', [CourseController::class, 'showQuiz'])->name('quiz.show');
    Route::post('/{course}/quizzes/{quiz}/submit', [CourseController::class, 'submitQuiz'])->name('quiz.submit');
    Route::post('/{id}/enroll', [CourseController::class, 'redirectEnroll'])->name('enroll');
    Route::post('/{id}/review', [CourseController::class, 'review'])->name('review');
});

Route::prefix('khoa-hoc')->name('khoa-hoc.')->group(function () {
    Route::post('/{id}/dang-ky', [CourseController::class, 'enrollSubject'])->name('enroll');
    Route::get('/{id}', [CourseController::class, 'showSubject'])->name('show');
});

Route::prefix('subjects')->group(function () {
    Route::post('/{id}/enroll', [CourseController::class, 'enrollSubject']);
    Route::get('/{id}', [CourseController::class, 'legacySubjectShow']);
});

Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicPageController::class, 'sendContact'])->name('contact.post');
Route::get('/blog', [PublicPageController::class, 'blog'])->name('blog');
Route::get('/teachers', [PublicPageController::class, 'teachers'])->name('teachers');
Route::get('/careers', [PublicPageController::class, 'careers'])->name('careers');
Route::get('/help', [PublicPageController::class, 'help'])->name('help');
Route::get('/terms', [PublicPageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/apply-teacher', [PublicPageController::class, 'showApplyTeacher'])->name('apply-teacher');
Route::post('/apply-teacher', [PublicPageController::class, 'submitTeacherApplication'])->name('apply-teacher.post');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::prefix('verify-email')->group(function () {
    Route::get('/', [AuthController::class, 'showVerifyEmail'])->name('verify.email');
    Route::post('/', [AuthController::class, 'verifyEmail'])->name('verify.email.post');
    Route::post('/resend', [AuthController::class, 'resendVerifyEmail'])->name('verify.email.resend');
});

Route::prefix('forgot-password')->group(function () {
    Route::get('/', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/', [AuthController::class, 'sendResetOtp'])->name('password.email');
    Route::get('/verify', [AuthController::class, 'showForgotVerify'])->name('forgot.verify');
    Route::post('/verify', [AuthController::class, 'verifyForgotOtp'])->name('forgot.verify.post');
    Route::get('/reset', [AuthController::class, 'showForgotReset'])->name('forgot.reset');
    Route::post('/reset', [AuthController::class, 'resetPassword'])->name('forgot.reset.post');
});

Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

Route::prefix('certificates')->name('certificates.')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('index');
    Route::get('/{id}', [CertificateController::class, 'show'])->name('show');
});

Route::prefix('student')->name('student.')->group(function () {
    Route::get('/schedule', [StudentController::class, 'schedule'])->name('schedule');
    Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
});

Route::prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/courses', [TeacherController::class, 'courses'])->name('courses');
    Route::get('/courses/{id}', [TeacherController::class, 'showCourse'])->name('course.show');
    Route::post('/grades', [TeacherController::class, 'updateGrades'])->name('grades.update');
    Route::get('/schedule-change-requests', [TeacherScheduleChangeRequestController::class, 'index'])->name('schedule-change-requests.index');
    Route::get('/courses/{course}/schedule-change-requests/create', [TeacherScheduleChangeRequestController::class, 'create'])->name('schedule-change-requests.create');
    Route::post('/courses/{course}/schedule-change-requests', [TeacherScheduleChangeRequestController::class, 'store'])->name('schedule-change-requests.store');
});

Route::prefix('admin')->name('admin.')->middleware('admin')->group(base_path('routes/admin.php'));

Route::get('/logout', [HomeController::class, 'logout'])->name('logout');
Route::fallback([HomeController::class, 'fallback']);
<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
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
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/report', [AdminController::class, 'report'])->name('report');

    Route::prefix('teacher-applications')->group(function () {
        Route::get('/', [AdminController::class, 'teacherApplications'])->name('teacher-applications');
        Route::get('/{id}', [AdminController::class, 'showTeacherApplication'])->name('teacher-applications.show');
        Route::post('/{id}/review', [AdminController::class, 'reviewTeacherApplication'])->name('teacher-applications.review');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [AdminController::class, 'users'])->name('users');
        Route::get('/{id}', [AdminController::class, 'showUser'])->name('user.show');
        Route::post('/', [AdminController::class, 'storeUser'])->name('users.create');
        Route::post('/{id}/update', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/{id}/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [AdminController::class, 'categories'])->name('categories');
        Route::post('/', [AdminController::class, 'storeCategory'])->name('categories.create');
        Route::post('/{id}/update', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::post('/{id}/delete', [AdminController::class, 'deleteCategory'])->name('categories.delete');
    });

    Route::prefix('subjects')->group(function () {
        Route::get('/', [AdminController::class, 'subjects'])->name('subjects');
        Route::get('/{id}', [AdminController::class, 'showSubject'])->name('subject.show');
        Route::post('/', [AdminController::class, 'storeSubject'])->name('subjects.create');
        Route::post('/{id}/update', [AdminController::class, 'updateSubject'])->name('subjects.update');
        Route::post('/{id}/delete', [AdminController::class, 'deleteSubject'])->name('subjects.delete');
        Route::post('/{subject_id}/courses', [AdminController::class, 'storeSubjectCourse'])->name('subject.courses.create');
    });

    Route::prefix('courses')->group(function () {
        Route::get('/', [AdminController::class, 'courses'])->name('courses');
        Route::get('/{id}', [AdminController::class, 'showCourse'])->name('course.show');
        Route::post('/', [AdminController::class, 'storeCourse'])->name('courses.create');
        Route::post('/{id}/update', [AdminController::class, 'updateCourse'])->name('courses.update');
        Route::post('/{id}/delete', [AdminController::class, 'deleteCourse'])->name('courses.delete');
        Route::post('/{id}/assign', [AdminController::class, 'assignCourse'])->name('courses.assign');
        Route::post('/{id}/modules', [AdminController::class, 'storeCourseModule'])->name('courses.modules.create');
    });

    Route::prefix('enrollments')->group(function () {
        Route::get('/', [AdminController::class, 'enrollments'])->name('enrollments');
        Route::post('/{id}/update', [AdminController::class, 'updateEnrollment'])->name('enrollments.update');
    });
});

Route::get('/logout', [HomeController::class, 'logout'])->name('logout');
Route::fallback([HomeController::class, 'fallback']);
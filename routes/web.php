<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicPageController;
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

Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

Route::prefix('certificates')->name('certificates.')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('index');
    Route::get('/{id}', [CertificateController::class, 'show'])->name('show');
});

require base_path('routes/auth.php');
Route::prefix('student')->name('student.')->middleware('student')->group(base_path('routes/student.php'));
Route::prefix('teacher')->name('teacher.')->middleware('teacher')->group(base_path('routes/teacher.php'));
Route::prefix('admin')->name('admin.')->middleware('admin')->group(base_path('routes/admin.php'));

Route::fallback([HomeController::class, 'fallback']);

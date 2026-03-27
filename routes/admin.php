<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminCourseModuleController;
use App\Http\Controllers\AdminEnrollmentController;
use App\Http\Controllers\AdminScheduleChangeRequestController;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AdminStudyGroupController;
use App\Http\Controllers\AdminSubjectController;
use App\Http\Controllers\AdminTeacherApplicationController;
use App\Http\Controllers\AdminTeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
Route::get('/reports', [AdminReportController::class, 'index'])->name('report');
Route::get('/report', fn () => redirect()->route('admin.report'))->name('report.legacy');

Route::prefix('students')->name('students.')->group(function () {
    Route::get('/', [AdminStudentController::class, 'index'])->name('index');
    Route::get('/create', [AdminStudentController::class, 'create'])->name('create');
    Route::post('/', [AdminStudentController::class, 'store'])->name('store');
    Route::get('/{student}', [AdminStudentController::class, 'show'])->name('show');
    Route::get('/{student}/edit', [AdminStudentController::class, 'edit'])->name('edit');
    Route::post('/{student}/update', [AdminStudentController::class, 'update'])->name('update');
    Route::post('/{student}/lock', [AdminStudentController::class, 'lock'])->name('lock');
    Route::post('/{student}/unlock', [AdminStudentController::class, 'unlock'])->name('unlock');
});

Route::prefix('teachers')->name('teachers.')->group(function () {
    Route::get('/', [AdminTeacherController::class, 'index'])->name('index');
    Route::get('/create', [AdminTeacherController::class, 'create'])->name('create');
    Route::post('/', [AdminTeacherController::class, 'store'])->name('store');
    Route::get('/{teacher}', [AdminTeacherController::class, 'show'])->name('show');
    Route::get('/{teacher}/edit', [AdminTeacherController::class, 'edit'])->name('edit');
    Route::post('/{teacher}/update', [AdminTeacherController::class, 'update'])->name('update');
    Route::post('/{teacher}/lock', [AdminTeacherController::class, 'lock'])->name('lock');
    Route::post('/{teacher}/unlock', [AdminTeacherController::class, 'unlock'])->name('unlock');
});

Route::prefix('teacher-applications')->group(function () {
    Route::get('/', [AdminTeacherApplicationController::class, 'index'])->name('teacher-applications');
    Route::get('/{teacherApplication}', [AdminTeacherApplicationController::class, 'show'])->name('teacher-applications.show');
    Route::post('/{teacherApplication}/review', [AdminTeacherApplicationController::class, 'review'])->name('teacher-applications.review');
});

Route::prefix('users')->group(function () {
    Route::get('/', [AdminController::class, 'users'])->name('users');
    Route::get('/{id}', [AdminController::class, 'showUser'])->name('user.show');
    Route::post('/', [AdminController::class, 'storeUser'])->name('users.create');
    Route::post('/{id}/update', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/{id}/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
});

Route::prefix('categories')->group(function () {
    Route::get('/', [AdminStudyGroupController::class, 'index'])->name('categories');
    Route::get('/create', [AdminStudyGroupController::class, 'create'])->name('categories.create-page');
    Route::post('/', [AdminStudyGroupController::class, 'store'])->name('categories.create');
    Route::get('/{category}', [AdminStudyGroupController::class, 'show'])->name('categories.show');
    Route::get('/{category}/edit', [AdminStudyGroupController::class, 'edit'])->name('categories.edit');
    Route::post('/{category}/update', [AdminStudyGroupController::class, 'update'])->name('categories.update');
    Route::post('/{category}/activate', [AdminStudyGroupController::class, 'activate'])->name('categories.activate');
    Route::post('/{category}/deactivate', [AdminStudyGroupController::class, 'deactivate'])->name('categories.deactivate');
    Route::post('/{category}/delete', [AdminStudyGroupController::class, 'deactivate'])->name('categories.delete');
});

Route::prefix('subjects')->group(function () {
    Route::get('/', [AdminSubjectController::class, 'index'])->name('subjects');
    Route::get('/create', [AdminSubjectController::class, 'create'])->name('subjects.create-page');
    Route::post('/', [AdminSubjectController::class, 'store'])->name('subjects.create');
    Route::get('/{subject}/edit', [AdminSubjectController::class, 'edit'])->name('subjects.edit');
    Route::get('/{subject}', [AdminSubjectController::class, 'show'])->name('subject.show');
    Route::post('/{subject}/update', [AdminSubjectController::class, 'update'])->name('subjects.update');
    Route::post('/{subject}/archive', [AdminSubjectController::class, 'archive'])->name('subjects.archive');
    Route::post('/{subject}/reopen', [AdminSubjectController::class, 'reopen'])->name('subjects.reopen');
    Route::post('/{subject}/delete', [AdminSubjectController::class, 'archive'])->name('subjects.delete');
    Route::post('/{subject}/courses', [AdminController::class, 'storeSubjectCourse'])->name('subject.courses.create');
});

Route::prefix('courses')->group(function () {
    Route::get('/', [AdminController::class, 'courses'])->name('courses');
    Route::get('/{course}/modules', [AdminCourseModuleController::class, 'index'])->name('courses.modules.index');
    Route::post('/{course}/modules', [AdminCourseModuleController::class, 'store'])->name('courses.modules.create');
    Route::post('/{course}/modules/reorder', [AdminCourseModuleController::class, 'reorder'])->name('courses.modules.reorder');
    Route::get('/{course}/modules/{module}/edit', [AdminCourseModuleController::class, 'edit'])->name('courses.modules.edit');
    Route::post('/{course}/modules/{module}/update', [AdminCourseModuleController::class, 'update'])->name('courses.modules.update');
    Route::post('/{course}/modules/{module}/delete', [AdminCourseModuleController::class, 'destroy'])->name('courses.modules.delete');
    Route::get('/{id}', [AdminController::class, 'showCourse'])->name('course.show');
    Route::post('/', [AdminController::class, 'storeCourse'])->name('courses.create');
    Route::post('/{id}/update', [AdminController::class, 'updateCourse'])->name('courses.update');
    Route::post('/{id}/delete', [AdminController::class, 'deleteCourse'])->name('courses.delete');
    Route::post('/{id}/assign', [AdminController::class, 'assignCourse'])->name('courses.assign');
});

Route::prefix('enrollments')->group(function () {
    Route::get('/', [AdminEnrollmentController::class, 'index'])->name('enrollments');
    Route::get('/{enrollment}', [AdminEnrollmentController::class, 'show'])->name('enrollments.show');
    Route::post('/{enrollment}/review', [AdminEnrollmentController::class, 'review'])->name('enrollments.review');
    Route::post('/{enrollment}/update', [AdminEnrollmentController::class, 'review'])->name('enrollments.update');
});

Route::prefix('schedules')->name('schedules.')->group(function () {
    Route::get('/', [AdminScheduleController::class, 'index'])->name('index');
    Route::get('/queue', [AdminScheduleController::class, 'queue'])->name('queue');
    Route::get('/enrollments/{enrollment}', [AdminScheduleController::class, 'showEnrollment'])->name('enrollments.show');
    Route::post('/enrollments/{enrollment}', [AdminScheduleController::class, 'storeEnrollment'])->name('enrollments.store');
});

Route::prefix('schedule-change-requests')->name('schedule-change-requests.')->group(function () {
    Route::get('/', [AdminScheduleChangeRequestController::class, 'index'])->name('index');
    Route::get('/{scheduleChangeRequest}', [AdminScheduleChangeRequestController::class, 'show'])->name('show');
    Route::post('/{scheduleChangeRequest}/review', [AdminScheduleChangeRequestController::class, 'review'])->name('review');
});
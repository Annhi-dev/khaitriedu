<?php

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\CourseTimeSlotController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\ModuleOverviewController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ScheduleChangeRequestController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SlotRegistrationController;
use App\Http\Controllers\Admin\SlotTrackingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudyGroupController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherApplicationController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/reports', [ReportController::class, 'index'])->name('report');
Route::get('/report', fn () => redirect()->route('admin.report'))->name('report.legacy');

Route::prefix('students')->name('students.')->group(function () {
    Route::get('/', [StudentController::class, 'index'])->name('index');
    Route::get('/create', [StudentController::class, 'create'])->name('create');
    Route::post('/', [StudentController::class, 'store'])->name('store');
    Route::get('/{student}', [StudentController::class, 'show'])->name('show');
    Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
    Route::post('/{student}/update', [StudentController::class, 'update'])->name('update');
    Route::post('/{student}/lock', [StudentController::class, 'lock'])->name('lock');
    Route::post('/{student}/unlock', [StudentController::class, 'unlock'])->name('unlock');
});

Route::prefix('teachers')->name('teachers.')->group(function () {
    Route::get('/', [TeacherController::class, 'index'])->name('index');
    Route::get('/create', [TeacherController::class, 'create'])->name('create');
    Route::post('/', [TeacherController::class, 'store'])->name('store');
    Route::get('/{teacher}', [TeacherController::class, 'show'])->name('show');
    Route::get('/{teacher}/edit', [TeacherController::class, 'edit'])->name('edit');
    Route::post('/{teacher}/update', [TeacherController::class, 'update'])->name('update');
    Route::post('/{teacher}/lock', [TeacherController::class, 'lock'])->name('lock');
    Route::post('/{teacher}/unlock', [TeacherController::class, 'unlock'])->name('unlock');
});

Route::prefix('teacher-applications')->group(function () {
    Route::get('/', [TeacherApplicationController::class, 'index'])->name('teacher-applications');
    Route::get('/{teacherApplication}', [TeacherApplicationController::class, 'show'])->name('teacher-applications.show');
    Route::post('/{teacherApplication}/review', [TeacherApplicationController::class, 'review'])->name('teacher-applications.review');
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users');
    Route::get('/{id}', [UserController::class, 'show'])->name('user.show');
    Route::post('/', [UserController::class, 'store'])->name('users.create');
    Route::post('/{id}/update', [UserController::class, 'update'])->name('users.update');
    Route::post('/{id}/delete', [UserController::class, 'destroy'])->name('users.delete');
});

Route::prefix('categories')->group(function () {
    Route::get('/', [StudyGroupController::class, 'index'])->name('categories');
    Route::get('/create', [StudyGroupController::class, 'create'])->name('categories.create-page');
    Route::post('/', [StudyGroupController::class, 'store'])->name('categories.create');
    Route::get('/{category}', [StudyGroupController::class, 'show'])->name('categories.show');
    Route::get('/{category}/edit', [StudyGroupController::class, 'edit'])->name('categories.edit');
    Route::post('/{category}/update', [StudyGroupController::class, 'update'])->name('categories.update');
    Route::post('/{category}/activate', [StudyGroupController::class, 'activate'])->name('categories.activate');
    Route::post('/{category}/deactivate', [StudyGroupController::class, 'deactivate'])->name('categories.deactivate');
    Route::post('/{category}/delete', [StudyGroupController::class, 'deactivate'])->name('categories.delete');
});

Route::prefix('subjects')->group(function () {
    Route::get('/', [SubjectController::class, 'index'])->name('subjects');
    Route::get('/create', [SubjectController::class, 'create'])->name('subjects.create-page');
    Route::post('/', [SubjectController::class, 'store'])->name('subjects.create');
    Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::get('/{subject}', [SubjectController::class, 'show'])->name('subject.show');
    Route::post('/{subject}/update', [SubjectController::class, 'update'])->name('subjects.update');
    Route::post('/{subject}/archive', [SubjectController::class, 'archive'])->name('subjects.archive');
    Route::post('/{subject}/reopen', [SubjectController::class, 'reopen'])->name('subjects.reopen');
    Route::post('/{subject}/delete', [SubjectController::class, 'archive'])->name('subjects.delete');
    Route::post('/{subject}/courses', [CourseController::class, 'storeSubjectCourse'])->name('subject.courses.create');
});

Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index'])->name('courses');
    Route::get('/{course}/modules', [ModuleController::class, 'index'])->name('courses.modules.index');
    Route::post('/{course}/modules', [ModuleController::class, 'store'])->name('courses.modules.create');
    Route::post('/{course}/modules/reorder', [ModuleController::class, 'reorder'])->name('courses.modules.reorder');
    Route::get('/{course}/modules/{module}/edit', [ModuleController::class, 'edit'])->name('courses.modules.edit');
    Route::post('/{course}/modules/{module}/update', [ModuleController::class, 'update'])->name('courses.modules.update');
    Route::post('/{course}/modules/{module}/delete', [ModuleController::class, 'destroy'])->name('courses.modules.delete');
    Route::get('/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::post('/', [CourseController::class, 'store'])->name('courses.create');
    Route::post('/{course}/update', [CourseController::class, 'update'])->name('courses.update');
    Route::post('/{course}/delete', [CourseController::class, 'destroy'])->name('courses.delete');
    Route::post('/{course}/assign', [CourseController::class, 'assign'])->name('courses.assign');
});

Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', [ModuleOverviewController::class, 'index'])->name('index');
});

Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/', [RoomController::class, 'index'])->name('index');
    Route::get('/create', [RoomController::class, 'create'])->name('create');
    Route::post('/', [RoomController::class, 'store'])->name('store');
    Route::get('/{room}/edit', [RoomController::class, 'edit'])->name('edit');
    Route::post('/{room}/update', [RoomController::class, 'update'])->name('update');
});

Route::prefix('course-time-slots')->name('course-time-slots.')->group(function () {
    Route::get('/', [CourseTimeSlotController::class, 'index'])->name('index');
    Route::get('/create', [CourseTimeSlotController::class, 'create'])->name('create');
    Route::post('/', [CourseTimeSlotController::class, 'store'])->name('store');
    Route::get('/{courseTimeSlot}/edit', [CourseTimeSlotController::class, 'edit'])->name('edit');
    Route::post('/{courseTimeSlot}/update', [CourseTimeSlotController::class, 'update'])->name('update');
});

Route::prefix('slot-registrations')->name('slot-registrations.')->group(function () {
    Route::get('/', [SlotRegistrationController::class, 'index'])->name('index');
    Route::get('/{slotRegistration}', [SlotRegistrationController::class, 'show'])->name('show');
});

Route::prefix('slot-tracking')->name('slot-tracking.')->group(function () {
    Route::get('/', [SlotTrackingController::class, 'index'])->name('index');
});

Route::prefix('enrollments')->group(function () {
    Route::get('/', [EnrollmentController::class, 'index'])->name('enrollments');
    Route::get('/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
    Route::post('/{enrollment}/review', [EnrollmentController::class, 'review'])->name('enrollments.review');
    Route::post('/{enrollment}/update', [EnrollmentController::class, 'review'])->name('enrollments.update');
});

Route::prefix('schedules')->name('schedules.')->group(function () {
    Route::get('/', [ScheduleController::class, 'index'])->name('index');
    Route::get('/queue', [ScheduleController::class, 'queue'])->name('queue');
    Route::get('/enrollments/{enrollment}', [ScheduleController::class, 'showEnrollment'])->name('enrollments.show');
    Route::post('/enrollments/{enrollment}', [ScheduleController::class, 'storeEnrollment'])->name('enrollments.store');
});

Route::prefix('schedule-change-requests')->name('schedule-change-requests.')->group(function () {
    Route::get('/', [ScheduleChangeRequestController::class, 'index'])->name('index');
    Route::get('/{scheduleChangeRequest}', [ScheduleChangeRequestController::class, 'show'])->name('show');
    Route::post('/{scheduleChangeRequest}/review', [ScheduleChangeRequestController::class, 'review'])->name('review');
});

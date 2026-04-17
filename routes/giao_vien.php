<?php

use App\Http\Controllers\Teacher\CourseController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\LeaveRequestController;
use App\Http\Controllers\Teacher\NotificationController;
use App\Http\Controllers\Teacher\ScheduleController;
use App\Http\Controllers\Teacher\ScheduleChangeRequestController;
use App\Http\Controllers\Teacher\TeacherClassroomController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/poll', [NotificationController::class, 'poll'])->name('poll');
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
});
Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/schedules/{schedule}/change-requests', [ScheduleChangeRequestController::class, 'storeForSchedule'])->name('schedules.change-requests.store');
Route::get('/classes', [TeacherClassroomController::class, 'index'])->name('classes.index');
Route::get('/classes/{classRoom}', [TeacherClassroomController::class, 'show'])->name('classes.show');
Route::post('/classes/{classRoom}/attendance', [TeacherClassroomController::class, 'storeAttendance'])->name('classes.attendance.store');
Route::post('/classes/{classRoom}/grades', [TeacherClassroomController::class, 'storeGrades'])->name('classes.grades.store');
Route::post('/classes/{classRoom}/evaluations', [TeacherClassroomController::class, 'storeEvaluation'])->name('classes.evaluations.store');
Route::get('/courses', [CourseController::class, 'courses'])->name('courses');
Route::get('/courses/{id}', [CourseController::class, 'showCourse'])->name('course.show');
Route::post('/grades', [CourseController::class, 'updateGrades'])->name('grades.update');
Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
    Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
    Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
    Route::post('/{leaveRequest}/review', [LeaveRequestController::class, 'review'])->name('review');
});
Route::get('/schedule-change-requests', [ScheduleChangeRequestController::class, 'index'])->name('schedule-change-requests.index');
Route::get('/courses/{course}/schedule-change-requests/create', [ScheduleChangeRequestController::class, 'create'])->name('schedule-change-requests.create');
Route::post('/courses/{course}/schedule-change-requests', [ScheduleChangeRequestController::class, 'store'])->name('schedule-change-requests.store');

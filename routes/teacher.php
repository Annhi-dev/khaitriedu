<?php

use App\Http\Controllers\Teacher\CourseController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\ScheduleChangeRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/courses', [CourseController::class, 'courses'])->name('courses');
Route::get('/courses/{id}', [CourseController::class, 'showCourse'])->name('course.show');
Route::post('/grades', [CourseController::class, 'updateGrades'])->name('grades.update');
Route::get('/schedule-change-requests', [ScheduleChangeRequestController::class, 'index'])->name('schedule-change-requests.index');
Route::get('/courses/{course}/schedule-change-requests/create', [ScheduleChangeRequestController::class, 'create'])->name('schedule-change-requests.create');
Route::post('/courses/{course}/schedule-change-requests', [ScheduleChangeRequestController::class, 'store'])->name('schedule-change-requests.store');

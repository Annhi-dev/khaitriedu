<?php

use App\Http\Controllers\Student\ClassEnrollController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\LeaveRequestController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\GradeController;
use App\Http\Controllers\Student\ScheduleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/poll', [NotificationController::class, 'poll'])->name('poll');
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
});
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
Route::get('/grades', [GradeController::class, 'index'])->name('grades');
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
    Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
    Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
    Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
    Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
});

Route::prefix('enroll')->name('enroll.')->group(function () {
    Route::get('/', [ClassEnrollController::class, 'index'])->name('index');
    Route::get('/my-classes', [ClassEnrollController::class, 'myClasses'])->name('my-classes');
    Route::get('/{subject}/select', [ClassEnrollController::class, 'selectClass'])->name('select');
    Route::get('/{subject}/request', [ClassEnrollController::class, 'requestForm'])->name('request-form');
    Route::post('/{subject}/store', [ClassEnrollController::class, 'store'])->name('store');
    Route::post('/{subject}/request', [ClassEnrollController::class, 'storeCustomRequest'])->name('request-store');
});

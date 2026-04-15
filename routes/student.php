<?php

use App\Http\Controllers\Student\ClassEnrollController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\GradeController;
use App\Http\Controllers\Student\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
Route::get('/grades', [GradeController::class, 'index'])->name('grades');

Route::prefix('enroll')->name('enroll.')->group(function () {
    Route::get('/', [ClassEnrollController::class, 'index'])->name('index');
    Route::get('/my-classes', [ClassEnrollController::class, 'myClasses'])->name('my-classes');
    Route::get('/{subject}/select', [ClassEnrollController::class, 'selectClass'])->name('select');
    Route::get('/{subject}/request', [ClassEnrollController::class, 'requestForm'])->name('request-form');
    Route::post('/{subject}/store', [ClassEnrollController::class, 'store'])->name('store');
    Route::post('/{subject}/request', [ClassEnrollController::class, 'storeCustomRequest'])->name('request-store');
});

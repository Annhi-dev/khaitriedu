<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\GradeController;
use App\Http\Controllers\Student\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
Route::get('/grades', [GradeController::class, 'index'])->name('grades');

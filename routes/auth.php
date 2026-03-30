<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

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

Route::post('/logout', [HomeController::class, 'logout'])->name('logout');

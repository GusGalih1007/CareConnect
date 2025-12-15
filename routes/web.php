<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Auth
// Register
Route::get('register', [AuthController::class, 'registerPage'])->name('register.form');
Route::post('register', [AuthController::class, 'register'])->name('register.post');

// Login
Route::get('login', [AuthController::class, 'loginPage'])->name('login.form');
Route::post('login', [AuthController::class, 'login'])->name('login.post');

// Forgot Password
Route::get('forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('forgot-password.form');
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.post');

// Reset Password
Route::get('reset-password', [AuthController::class, 'resetPasswordForm'])->name('reset-password.form');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.post');

// Verify OTP
Route::get('verify-otp', [AuthController::class, 'verifyOtpForm'])->name('verify-otp.form');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp.post');
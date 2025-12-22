<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LocationController;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return redirect()->route('login.form');
});
Route::get('/welcome', function () {
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
Route::get('verify-otp/reset', [AuthController::class, 'resetOtp'])->name('reset.otp');
Route::get('verify-otp', [AuthController::class, 'verifyOtpForm'])->name('verify-otp.form');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp.post');

// Logout

Route::middleware('user.status')->group(function () {
    Route::get('admin', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    Route::get("admin/profile", [AuthController::class, 'showProfile'])->name('user.profile');
    Route::put("admin/profile/change-password", [AuthController::class, 'changePassword'])->name('user.profile.changePassword');
    Route::put("admin/profile/profile-update", [AuthController::class, 'updateProfile'])->name('user.profile.update');
    Route::post('admin/profile/add-address', [LocationController::class, 'store'])->name('user.location.store');
    Route::delete('admin/profile/delete-address/{id}', [LocationController::class, 'destroy'])->name('user.location.delete');
});

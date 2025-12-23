<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CategoryController;
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
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('admin')->group(function () {
        // Profile
        Route::get('/', [DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::get("/profile", [AuthController::class, 'showProfile'])->name('admin.profile');
        Route::put("/profile/change-password", [AuthController::class, 'changePassword'])->name('admin.profile.changePassword');
        Route::put("/profile/profile-update", [AuthController::class, 'updateProfile'])->name('admin.profile.update');
        Route::post('/profile/add-address', [LocationController::class, 'store'])->name('admin.location.store');
        Route::delete('/profile/delete-address/{id}', [LocationController::class, 'destroy'])->name('admin.location.delete');

        //category
        Route::get('category', [CategoryController::class, 'index'])->name('admin.category.index');
        Route::get('category/create', [CategoryController::class, 'create'])->name('admin.category.create');
        Route::get('category/trashed', [CategoryController::class, 'trashed'])->name('admin.category.trash');
        Route::get('category/trashed/restore/{id}', [CategoryController::class, 'restoreById'])->name('admin.category.restoreOne');
        Route::get('category/trashed/restore-all', [CategoryController::class, 'restoreAll'])->name('admin.category.restoreAll');
        Route::post('category', [CategoryController::class, 'store'])->name('admin.category.store');
        Route::get('category/{id}/edit', [CategoryController::class, 'edit'])->name('admin.category.edit');
        Route::put('category/{id}', [CategoryController::class, 'update'])->name('admin.category.update');
        Route::delete('category/{id}', [CategoryController::class, 'destroy'])->name('admin.category.delete');
    });
    

    
});

<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DonationController;
use App\Http\Controllers\Web\DonationRequestController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\MatchController;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login.form');
});

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


Route::middleware('user.status')->group(function () {

    // Logout
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    // Admin route
    Route::prefix('admin')->group(function () {

        // Profile
        Route::get('/', [DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::get('/profile', [AuthController::class, 'showProfile'])->name('admin.profile');
        Route::put('/profile/change-password', [AuthController::class, 'changePassword'])->name('admin.profile.changePassword');
        Route::put('/profile/profile-update', [AuthController::class, 'updateProfile'])->name('admin.profile.update');
        Route::post('/profile/add-address', [LocationController::class, 'store'])->name('admin.location.store');
        Route::delete('/profile/delete-address/{id}', [LocationController::class, 'destroy'])->name('admin.location.delete');


        // category
        Route::prefix('category')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('admin.category.index');
            Route::get('/create', [CategoryController::class, 'create'])->name('admin.category.create');
            Route::get('/trashed', [CategoryController::class, 'trashed'])->name('admin.category.trash');
            Route::get('/trashed/restore/{id}', [CategoryController::class, 'restoreById'])->name('admin.category.restoreOne');
            Route::get('/trashed/restore-all', [CategoryController::class, 'restoreAll'])->name('admin.category.restoreAll');
            Route::post('/', [CategoryController::class, 'store'])->name('admin.category.store');
            Route::get('/{id}/edit', [CategoryController::class, 'edit'])->name('admin.category.edit');
            Route::put('/{id}', [CategoryController::class, 'update'])->name('admin.category.update');
            Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('admin.category.delete');
        });


        // donation request
        Route::prefix('donation')->group(function () {
            Route::get('/', [DonationController::class, 'index'])->name('admin.donation.index');
            Route::get('/create', [DonationController::class, 'create'])->name('admin.donation.create');
            Route::post('/', [DonationController::class, 'store'])->name('admin.donation.store');
            Route::get('/{id}', [DonationController::class, 'show'])->name('admin.donation.show');
            Route::get('/{id}/edit', [DonationController::class, 'edit'])->name('admin.donation.edit');
            Route::put('/{id}', [DonationController::class, 'update'])->name('admin.donation.update');
            Route::delete('/{id}', [DonationController::class, 'destroy'])->name('admin.donation.destroy');

            // Offer to request
            Route::post('/{id}/offer', [DonationController::class, 'offerToRequest'])->name('admin.donation.offer');

            // Update status
            Route::put('/{id}/status', [DonationController::class, 'updateStatus'])->name('admin.donation.update-status');

            // Browse for requesters
            Route::get('/browse/all', [DonationController::class, 'browse'])->name('admin.donation.browse');
            Route::get('/browse/filter', [DonationController::class, 'filter'])->name('admin.donation.filter');
        });

        
        // Donation request
        Route::prefix('donation-request')->group(function () {
            Route::get('/', [DonationRequestController::class, 'index'])->name('admin.donation-request.index');
            Route::get('/create', [DonationRequestController::class, 'create'])->name('admin.donation-request.create');
            Route::post('/', [DonationRequestController::class, 'store'])->name('admin.donation-request.store');
            Route::get('/{id}', [DonationRequestController::class, 'show'])->name('admin.donation-request.show');
            Route::get('/{id}/edit', [DonationRequestController::class, 'edit'])->name('admin.donation-request.edit');
            Route::put('/{id}', [DonationRequestController::class, 'update'])->name('admin.donation-request.update');
            Route::delete('/{id}', [DonationRequestController::class, 'destroy'])->name('admin.donation-request.destroy');
    
            // Browse for donors
            Route::get('/browse/all', [DonationRequestController::class, 'browse'])->name('admin.donation-request.browse');
            Route::get('/browse/filter', [DonationRequestController::class, 'filter'])->name('admin.donation-request.filter');
        });
    
        
        // donation match
        Route::prefix('donation-match')->group(function () {
            Route::get('/', [MatchController::class, 'index'])->name('admin.donation-match.index');
            Route::get('/{id}', [MatchController::class, 'show'])->name('admin.donation-match.show');
            Route::post('/{id}/accept', [MatchController::class, 'accept'])->name('admin.donation-match.accept');
            Route::post('/{id}/reject', [MatchController::class, 'reject'])->name('admin.donation-match.reject');
    
            // For specific request/donation
            Route::get('/request/{requestId}', [MatchController::class, 'forRequest'])->name('admin.donation-match.for-request');
            Route::get('/donation/{donationId}', [MatchController::class, 'forDonation'])->name('admin.donation-match.for-donation');
        });
    });
});

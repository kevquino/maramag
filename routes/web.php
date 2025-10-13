<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BidsAwardsController;
use App\Http\Controllers\FullDisclosureController;
use App\Http\Controllers\TourismPackageController;
use App\Http\Controllers\AwardsRecognitionController;
use App\Http\Controllers\SangguniangBayanController;
use App\Http\Controllers\OrdinanceResolutionController;
use App\Http\Controllers\UserManagementController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])
    ->middleware(['auth'])
    ->name('dashboard.stats');

Route::middleware(['auth'])->group(function () {
    // News routes
    Route::resource('news', NewsController::class)->except(['show']);
    Route::post('/news/{news}/status', [NewsController::class, 'updateStatus'])->name('news.status');
    Route::post('/news/{news}/toggle-featured', [NewsController::class, 'toggleFeatured'])->name('news.toggle-featured');

    // Bids & Awards routes
    Route::resource('bids-awards', BidsAwardsController::class);
    Route::post('/bids-awards/{bids_award}/toggle-featured', [BidsAwardsController::class, 'toggleFeatured'])->name('bids-awards.toggle-featured');

    // Tourism Packages routes
    Route::resource('tourism', TourismPackageController::class);
    Route::post('/tourism/{tourism_package}/toggle-featured', [TourismPackageController::class, 'toggleFeatured'])->name('tourism.toggle-featured');
    Route::post('/tourism/{tourism_package}/toggle-status', [TourismPackageController::class, 'toggleStatus'])->name('tourism.toggle-status');

    // Awards & Recognition routes
    Route::resource('awards-recognition', AwardsRecognitionController::class);
    Route::post('/awards-recognition/{awards_recognition}/toggle-featured', [AwardsRecognitionController::class, 'toggleFeatured'])->name('awards-recognition.toggle-featured');
    Route::post('/awards-recognition/{awards_recognition}/toggle-status', [AwardsRecognitionController::class, 'toggleStatus'])->name('awards-recognition.toggle-status');

    // Sangguniang Bayan routes
    Route::resource('sangguniang-bayan', SangguniangBayanController::class);
    Route::post('/sangguniang-bayan/{sangguniang_bayan}/toggle-featured', [SangguniangBayanController::class, 'toggleFeatured'])->name('sangguniang-bayan.toggle-featured');
    Route::post('/sangguniang-bayan/{sangguniang_bayan}/toggle-status', [SangguniangBayanController::class, 'toggleStatus'])->name('sangguniang-bayan.toggle-status');
    Route::post('/sangguniang-bayan/{sangguniang_bayan}/update-order', [SangguniangBayanController::class, 'updateOrder'])->name('sangguniang-bayan.update-order');

    // User Management routes
    Route::resource('user-management', UserManagementController::class);
    Route::post('/user-management/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('user-management.toggle-status');
    Route::post('/user-management/{user}/resend-verification', [UserManagementController::class, 'resendVerification'])->name('user-management.resend-verification');
});

// Public show routes
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');
Route::get('/bids-awards/{bids_award}', [BidsAwardsController::class, 'show'])->name('bids-awards.show');
Route::get('/tourism/{tourism_package}', [TourismPackageController::class, 'show'])->name('tourism.show');
Route::get('/awards-recognition/{awards_recognition}', [AwardsRecognitionController::class, 'show'])->name('awards-recognition.show');
Route::get('/sangguniang-bayan/{sangguniang_bayan}', [SangguniangBayanController::class, 'show'])->name('sangguniang-bayan.show');

Route::middleware(['auth'])->group(function () {
    // Full Disclosure routes
    Route::resource('full-disclosure', FullDisclosureController::class);
    Route::get('/full-disclosure/{full_disclosure}/download', [FullDisclosureController::class, 'download'])->name('full-disclosure.download');
});

// Ordinance & Resolution routes
Route::resource('ordinance-resolutions', OrdinanceResolutionController::class);
Route::post('/ordinance-resolutions/{ordinance_resolution}/toggle-featured', [OrdinanceResolutionController::class, 'toggleFeatured'])->name('ordinance-resolutions.toggle-featured');
Route::post('/ordinance-resolutions/{ordinance_resolution}/toggle-status', [OrdinanceResolutionController::class, 'toggleStatus'])->name('ordinance-resolutions.toggle-status');
Route::get('/ordinance-resolutions/{ordinance_resolution}/download', [OrdinanceResolutionController::class, 'download'])->name('ordinance-resolutions.download');

// Public show route
Route::get('/ordinance-resolutions/{ordinance_resolution}', [OrdinanceResolutionController::class, 'show'])->name('ordinance-resolutions.show');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
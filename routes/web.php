<?php

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
use App\Http\Middleware\CheckPermission;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard routes - accessible to all authenticated users
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Enhanced dashboard API routes
Route::prefix('api/dashboard')->middleware(['auth'])->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/system-stats', [DashboardController::class, 'systemStats'])->name('dashboard.system-stats');
    Route::get('/recent-activities', [DashboardController::class, 'recentActivities'])->name('dashboard.recent-activities');
});

Route::middleware(['auth'])->group(function () {
    // News routes - require 'news' permission
    Route::middleware([CheckPermission::class . ':news'])->group(function () {
        Route::resource('news', NewsController::class)->except(['show']);
        Route::post('/news/{news}/status', [NewsController::class, 'updateStatus'])->name('news.status');
        Route::post('/news/{news}/toggle-featured', [NewsController::class, 'toggleFeatured'])->name('news.toggle-featured');
    });

    // Bids & Awards routes - require 'bids_awards' permission
    Route::middleware([CheckPermission::class . ':bids_awards'])->group(function () {
        Route::resource('bids-awards', BidsAwardsController::class);
        Route::post('/bids-awards/{bids_award}/toggle-featured', [BidsAwardsController::class, 'toggleFeatured'])->name('bids-awards.toggle-featured');
    });

    // Tourism Packages routes - require 'tourism' permission
    Route::middleware([CheckPermission::class . ':tourism'])->group(function () {
        Route::resource('tourism', TourismPackageController::class);
        Route::post('/tourism/{tourism_package}/toggle-featured', [TourismPackageController::class, 'toggleFeatured'])->name('tourism.toggle-featured');
        Route::post('/tourism/{tourism_package}/toggle-status', [TourismPackageController::class, 'toggleStatus'])->name('tourism.toggle-status');
    });

    // Awards & Recognition routes - require 'awards_recognition' permission
    Route::middleware([CheckPermission::class . ':awards_recognition'])->group(function () {
        Route::resource('awards-recognition', AwardsRecognitionController::class);
        Route::post('/awards-recognition/{awards_recognition}/toggle-featured', [AwardsRecognitionController::class, 'toggleFeatured'])->name('awards-recognition.toggle-featured');
        Route::post('/awards-recognition/{awards_recognition}/toggle-status', [AwardsRecognitionController::class, 'toggleStatus'])->name('awards-recognition.toggle-status');
    });

    // Sangguniang Bayan routes - require 'sangguniang_bayan' permission
    Route::middleware([CheckPermission::class . ':sangguniang_bayan'])->group(function () {
        Route::resource('sangguniang-bayan', SangguniangBayanController::class);
        Route::post('/sangguniang-bayan/{sangguniang_bayan}/toggle-featured', [SangguniangBayanController::class, 'toggleFeatured'])->name('sangguniang-bayan.toggle-featured');
        Route::post('/sangguniang-bayan/{sangguniang_bayan}/toggle-status', [SangguniangBayanController::class, 'toggleStatus'])->name('sangguniang-bayan.toggle-status');
        Route::post('/sangguniang-bayan/{sangguniang_bayan}/update-order', [SangguniangBayanController::class, 'updateOrder'])->name('sangguniang-bayan.update-order');
    });

    // User Management routes - require 'user_management' permission
    Route::middleware([CheckPermission::class . ':user_management'])->group(function () {
        Route::resource('user-management', UserManagementController::class);
        Route::post('/user-management/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('user-management.toggle-status');
        Route::post('/user-management/{user}/resend-verification', [UserManagementController::class, 'resendVerification'])->name('user-management.resend-verification');
        Route::post('/user-management/{user}/impersonate', [UserManagementController::class, 'impersonate'])->name('user-management.impersonate');
        Route::post('/user-management/stop-impersonate', [UserManagementController::class, 'stopImpersonate'])->name('user-management.stop-impersonate');
    });

    // Full Disclosure routes - require 'full_disclosure' permission
    Route::middleware([CheckPermission::class . ':full_disclosure'])->group(function () {
        Route::resource('full-disclosure', FullDisclosureController::class);
        Route::get('/full-disclosure/{full_disclosure}/download', [FullDisclosureController::class, 'download'])->name('full-disclosure.download');
    });

    // Ordinance & Resolution routes - require 'ordinance_resolutions' permission
    Route::middleware([CheckPermission::class . ':ordinance_resolutions'])->group(function () {
        Route::resource('ordinance-resolutions', OrdinanceResolutionController::class);
        Route::post('/ordinance-resolutions/{ordinance_resolution}/toggle-featured', [OrdinanceResolutionController::class, 'toggleFeatured'])->name('ordinance-resolutions.toggle-featured');
        Route::post('/ordinance-resolutions/{ordinance_resolution}/toggle-status', [OrdinanceResolutionController::class, 'toggleStatus'])->name('ordinance-resolutions.toggle-status');
        Route::get('/ordinance-resolutions/{ordinance_resolution}/download', [OrdinanceResolutionController::class, 'download'])->name('ordinance-resolutions.download');
    });

    // Activity Logs routes - require 'activity_logs' permission
    Route::middleware([CheckPermission::class . ':activity_logs'])->group(function () {
        Route::get('/activity-logs', function () {
            return Inertia::render('ActivityLogs/Index');
        })->name('activity-logs.index');
    });

    // Trash routes - require 'trash' permission
    Route::middleware([CheckPermission::class . ':trash'])->group(function () {
        Route::get('/trash', function () {
            return Inertia::render('Trash/Index');
        })->name('trash.index');
    });

    // Business Permit routes - require respective permissions
    Route::prefix('business-permit')->group(function () {
        // Main business permit page - require 'business_permit' permission
        Route::middleware([CheckPermission::class . ':business_permit'])->group(function () {
            Route::get('/', function () {
                return Inertia::render('BusinessPermit/Index');
            })->name('business-permit.index');
        });
        
        // New application - require 'new_application' permission
        Route::middleware([CheckPermission::class . ':new_application'])->group(function () {
            Route::get('/new-application', function () {
                return Inertia::render('BusinessPermit/NewApplication');
            })->name('business-permit.new-application');
        });
        
        // Renewal permit - require 'renewal_permit' permission
        Route::middleware([CheckPermission::class . ':renewal_permit'])->group(function () {
            Route::get('/renewal-permit', function () {
                return Inertia::render('BusinessPermit/RenewalPermit');
            })->name('business-permit.renewal-permit');
        });
    });
});

// Public show routes - no authentication required for viewing
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');
Route::get('/bids-awards/{bids_award}', [BidsAwardsController::class, 'show'])->name('bids-awards.show');
Route::get('/tourism/{tourism_package}', [TourismPackageController::class, 'show'])->name('tourism.show');
Route::get('/awards-recognition/{awards_recognition}', [AwardsRecognitionController::class, 'show'])->name('awards-recognition.show');
Route::get('/sangguniang-bayan/{sangguniang_bayan}', [SangguniangBayanController::class, 'show'])->name('sangguniang-bayan.show');
Route::get('/ordinance-resolutions/{ordinance_resolution}', [OrdinanceResolutionController::class, 'show'])->name('ordinance-resolutions.show');
Route::get('/full-disclosure/{full_disclosure}', [FullDisclosureController::class, 'show'])->name('full-disclosure.show');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BidsAwardsController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Update dashboard route to use controller
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Add API route for dashboard stats
Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])
    ->middleware(['auth'])
    ->name('dashboard.stats');

Route::middleware(['auth'])->group(function () {
    // News management routes - using resource for standard CRUD
    Route::resource('news', NewsController::class)->except(['show']);
    
    // Additional news management routes
    Route::post('/news/{news}/status', [NewsController::class, 'updateStatus'])->name('news.status');
    Route::post('/news/{news}/toggle-featured', [NewsController::class, 'toggleFeatured'])->name('news.toggle-featured');

    // Bids & Awards management routes
    Route::resource('bids-awards', BidsAwardsController::class);
    Route::post('/bids-awards/{bids_award}/toggle-featured', [BidsAwardsController::class, 'toggleFeatured'])->name('bids-awards.toggle-featured');
});

// Public show routes (accessible without auth if needed)
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');
Route::get('/bids-awards/{bids_award}', [BidsAwardsController::class, 'show'])->name('bids-awards.show');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
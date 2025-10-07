<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\DashboardController;

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
});

// Public show route (accessible without auth if needed)
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
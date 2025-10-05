<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NewsController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// News Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('news', [NewsController::class, 'index'])->name('news.index');
    Route::post('news', [NewsController::class, 'store'])->name('news.store');
    Route::get('news/create', [NewsController::class, 'create'])->name('news.create');
    Route::get('news/{news}', [NewsController::class, 'show'])->name('news.show');
    Route::get('news/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('news/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');
    Route::patch('news/{news}/status', [NewsController::class, 'updateStatus'])->name('news.status');
    Route::patch('news/{news}/feature', [NewsController::class, 'toggleFeatured'])->name('news.feature');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
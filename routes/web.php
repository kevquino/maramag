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

// News Routes - Using Controller
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('news', [NewsController::class, 'index'])->name('news');
    Route::get('news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('news', [NewsController::class, 'store'])->name('news.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
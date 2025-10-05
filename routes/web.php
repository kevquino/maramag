<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Update this line to point to the settings folder
Route::get('news', function () {
    return Inertia::render('News');
})->middleware(['auth', 'verified'])->name('news');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
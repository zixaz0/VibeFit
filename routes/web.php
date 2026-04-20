<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodLogController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ── PUBLIC ────────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/register',       [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',      [AuthController::class, 'register']);
    Route::get('/login',          [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',         [AuthController::class, 'login']);
});

// ── AUTHENTICATED ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile',                  [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile',                  [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',         [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Food Logs
    Route::post('/food/analyze',            [FoodLogController::class, 'analyze'])->name('food.analyze');
    Route::post('/food',                    [FoodLogController::class, 'store'])->name('food.store');
    Route::delete('/food/{foodLog}',        [FoodLogController::class, 'destroy'])->name('food.destroy');
    Route::get('/food/history',             [FoodLogController::class, 'history'])->name('food.history');

    // // Serve private images
    // Route::get('/food/image/{path}',        [FoodLogController::class, 'serveImage'])
    //     ->where('path', '.*')
    //     ->name('food.image');
});

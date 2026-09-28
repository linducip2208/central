<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/docs', [DocsController::class, 'index'])->name('docs');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/2fa/challenge', [AuthController::class, 'showChallenge'])->name('2fa.challenge');
Route::post('/2fa/challenge', [AuthController::class, 'verifyChallenge'])->middleware('throttle:2fa')->name('2fa.verify');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/2fa/confirm', [ProfileController::class, 'confirm2fa'])->name('profile.2fa.confirm');
    Route::post('/profile/2fa/disable', [ProfileController::class, 'disable2fa'])->name('profile.2fa.disable');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/tokens', [ProfileController::class, 'createToken'])->name('profile.tokens.create');
    Route::delete('/profile/tokens/{id}', [ProfileController::class, 'revokeToken'])->name('profile.tokens.revoke');
    require __DIR__.'/modules.php';
});

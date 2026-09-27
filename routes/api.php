<?php

use App\Http\Controllers\Api\V1\AuthController as ApiAuth;
use App\Http\Controllers\Api\V1\DeliveryController as ApiDelivery;
use App\Http\Controllers\Api\V1\StockController as ApiStock;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok', 'app' => config('app.name'), 'version' => config('mbg.version')]));

Route::prefix('v1')->group(function () {
    Route::post('/token', [ApiAuth::class, 'token'])->middleware('throttle:login')->name('api.v1.token');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [ApiAuth::class, 'me'])->name('api.v1.me');
        Route::post('/logout', [ApiAuth::class, 'logout'])->name('api.v1.logout');

        Route::get('/stock/summary', [ApiStock::class, 'summary'])->name('api.v1.stock.summary');
        Route::get('/stock/low', [ApiStock::class, 'lowStock'])->name('api.v1.stock.low');
        Route::get('/stock/expiring', [ApiStock::class, 'expiring'])->name('api.v1.stock.expiring');
        Route::get('/stock/movements', [ApiStock::class, 'movements'])->name('api.v1.stock.movements');

        Route::get('/deliveries', [ApiDelivery::class, 'index'])->name('api.v1.deliveries.index');
        Route::get('/deliveries/{delivery}', [ApiDelivery::class, 'show'])->name('api.v1.deliveries.show');
        Route::post('/deliveries/{delivery}/track', [ApiDelivery::class, 'track'])->name('api.v1.deliveries.track');
    });
});

<?php

use App\Http\Controllers\Api\V1\AuthController as ApiAuth;
use App\Http\Controllers\Api\V1\CatalogController as ApiCatalog;
use App\Http\Controllers\Api\V1\DeliveryController as ApiDelivery;
use App\Http\Controllers\Api\V1\OperationsController as ApiOps;
use App\Http\Controllers\Api\V1\PlanningController as ApiPlanning;
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

        Route::get('/schools', [ApiCatalog::class, 'schools'])->name('api.v1.schools');
        Route::get('/schools/{school}', [ApiCatalog::class, 'schoolShow'])->name('api.v1.schools.show');
        Route::get('/recipients', [ApiCatalog::class, 'recipients'])->name('api.v1.recipients');
        Route::get('/allergens', [ApiCatalog::class, 'allergens'])->name('api.v1.allergens');

        Route::get('/demand-plans', [ApiPlanning::class, 'demandPlans'])->name('api.v1.demand-plans');
        Route::get('/mrp-runs', [ApiPlanning::class, 'mrpRuns'])->name('api.v1.mrp-runs');
        Route::get('/mrp-runs/{run}', [ApiPlanning::class, 'mrpShow'])->name('api.v1.mrp-runs.show');
        Route::get('/boms', [ApiPlanning::class, 'boms'])->name('api.v1.boms');
        Route::post('/bom-explode', [ApiPlanning::class, 'bomExplode'])->name('api.v1.bom-explode');

        Route::get('/production-orders', [ApiOps::class, 'productionOrders'])->name('api.v1.production');
        Route::get('/production-orders/{order}', [ApiOps::class, 'productionShow'])->name('api.v1.production.show');
        Route::get('/inspections', [ApiOps::class, 'inspections'])->name('api.v1.inspections');
        Route::get('/recalls', [ApiOps::class, 'recalls'])->name('api.v1.recalls');
        Route::get('/recalls/{recall}', [ApiOps::class, 'recallShow'])->name('api.v1.recalls.show');
        Route::get('/invoices', [ApiOps::class, 'invoices'])->name('api.v1.invoices');
    });
});

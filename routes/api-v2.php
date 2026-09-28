<?php

use Illuminate\Support\Facades\Route;

/*
 * API v2 — PREVIEW skeleton (belum stabil, non-breaking terhadap v1).
 * Kontrak: prefix /api/v2, resource yang sama dengan v1, header
 * `X-API-Version: 2`. Jangan dipakai produksi sebelum diumumkan GA.
 */
Route::prefix('v2')->group(function () {
    Route::get('/status', fn () => response()->json([
        'success' => true,
        'version' => '2-preview',
        'message' => 'API v2 dalam tahap preview. Gunakan v1 untuk produksi.',
    ]))->name('api.v2.status');
});

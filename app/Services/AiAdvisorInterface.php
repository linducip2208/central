<?php

namespace App\Services;

/**
 * AI-ready seam (Fase 29).
 *
 * - Implementasi default 100% deterministik (tidak butuh LLM).
 * - Adapter LLM di masa depan HANYA boleh mengembalikan struktur
 *   AdvisoryResult yang explainable.
 * - TIDAK ADA implementasi yang boleh memutasi inventory langsung;
 *   mutasi tetap lewat InventoryService + otorisasi normal.
 */
interface AiAdvisorInterface
{
    public function demandForecast(int $productId, int $horizonDays = 14): AdvisoryResult;

    public function expiryRisk(int $warehouseId): AdvisoryResult;

    public function wasteAnalysis(int $centralKitchenId, int $days = 30): AdvisoryResult;
}

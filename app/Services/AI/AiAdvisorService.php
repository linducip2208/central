<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AdvisoryResult;
use App\Services\DeterministicAdvisor;

/**
 * Fasade advisory: guard → konteks → provider (default deterministik)
 * → audit. Rekomendasi SELALU butuh persetujuan manusia sebelum aksi.
 */
class AiAdvisorService
{
    public function __construct(
        protected DeterministicAdvisor $deterministic,
        protected AiContextBuilder $context,
        protected AiPermissionGuard $guard,
        protected AiAuditLogger $audit,
        protected ?AiProviderInterface $llm = null,
    ) {}

    public function advise(User $user, string $topic, array $params = []): AdvisoryResult
    {
        abort_unless($this->guard->allowed($user, $topic), 403, 'Topik AI tidak diizinkan untuk peran Anda.');
        $ctx = $this->context->build($user, $topic, $params);

        $result = match (true) {
            $this->llm && $this->llm->isConfigured() => $this->llm->advise($topic, $ctx),
            default => $this->deterministicFor($topic, $ctx),
        };
        $this->audit->log($user, $topic, $result, $this->llm && $this->llm->isConfigured() ? $this->llm->name() : 'deterministic');

        return $result;
    }

    protected function deterministicFor(string $topic, array $ctx): AdvisoryResult
    {
        $kitchenId = (int) ($ctx['central_kitchen_id'] ?? 0);

        return match ($topic) {
            'demand_forecast' => $this->deterministic->demandForecast((int) ($ctx['params']['product_id'] ?? 0), (int) ($ctx['params']['horizon'] ?? 14)),
            'expiry_risk' => $this->deterministic->expiryRisk((int) ($ctx['params']['warehouse_id'] ?? 0)),
            'executive_summary' => new AdvisoryResult('executive_summary', 'Ringkasan deterministik: lihat KPI dashboard untuk angka terkini.', []),
            default => $this->deterministic->wasteAnalysis($kitchenId),
        };
    }
}

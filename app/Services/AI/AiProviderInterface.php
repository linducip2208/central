<?php

namespace App\Services\AI;

use App\Services\AdvisoryResult;

/**
 * Kontrak provider AI. Implementasi LLM masa depan wajib:
 * - hanya membaca konteks via AiContextBuilder,
 * - melewati AiPermissionGuard,
 * - mencatat via AiAuditLogger,
 * - TIDAK PERNAH mengeksekusi mutasi bisnis langsung.
 */
interface AiProviderInterface
{
    public function name(): string;

    public function advise(string $topic, array $context): AdvisoryResult;

    public function isConfigured(): bool;
}

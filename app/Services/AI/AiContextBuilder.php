<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\ForecastService;

/**
 * Membangun konteks data yang BOLEH dilihat provider AI:
 * agregat + ter-skup organisasi, tanpa data personal berlebih.
 */
class AiContextBuilder
{
    public function __construct(protected ForecastService $forecast) {}

    public function build(User $user, string $topic, array $params = []): array
    {
        $orgId = $user->organization_id;
        $kitchenId = $user->central_kitchen_id;

        return [
            'topic' => $topic,
            'organization_id' => $orgId,
            'central_kitchen_id' => $kitchenId,
            'params' => $params,
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}

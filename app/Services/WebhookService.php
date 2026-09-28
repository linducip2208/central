<?php

namespace App\Services;

use App\Jobs\DeliverWebhook;
use App\Models\Webhook;
use App\Models\WebhookDelivery;

class WebhookService
{
    public function dispatch(string $event, array $payload, ?int $organizationId = null): int
    {
        $query = Webhook::where('is_active', true)->whereJsonContains('events', $event);
        if ($organizationId !== null) {
            $query->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $organizationId));
        }
        $count = 0;
        foreach ($query->get() as $webhook) {
            if (! $webhook->subscribes($event)) {
                continue;
            }
            $delivery = WebhookDelivery::create([
                'webhook_id' => $webhook->id, 'event' => $event,
                'payload' => $payload, 'status' => 'PENDING',
            ]);
            DeliverWebhook::dispatch($delivery->id);
            $count++;
        }

        return $count;
    }

    public function signature(string $secret, array $payload): string
    {
        return 'sha256='.hash_hmac('sha256', json_encode($payload), $secret);
    }
}

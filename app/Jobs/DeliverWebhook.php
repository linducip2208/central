<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliverWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public readonly int $deliveryId) {}

    public function handle(WebhookService $service): void
    {
        $delivery = WebhookDelivery::with('webhook')->find($this->deliveryId);
        if (! $delivery || ! $delivery->canRetry()) {
            return;
        }
        $delivery->increment('attempts');

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-MBG-Event' => $delivery->event, 'X-MBG-Signature' => $service->signature($delivery->webhook->secret, $delivery->payload)])
                ->post($delivery->webhook->url, $delivery->payload);
            $delivery->update([
                'status_code' => $response->status(),
                'status' => $response->successful() ? 'DELIVERED' : 'FAILED',
                'last_error' => $response->successful() ? null : 'HTTP '.$response->status(),
                'delivered_at' => $response->successful() ? now() : null,
            ]);
            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status());
            }
        } catch (\Throwable $e) {
            Log::warning('Webhook delivery failed', ['id' => $delivery->id, 'error' => $e->getMessage()]);
            $delivery->update([
                'status' => $delivery->attempts >= 5 ? 'DEAD' : 'PENDING',
                'last_error' => substr($e->getMessage(), 0, 500),
            ]);
            if ($delivery->attempts < 5) {
                throw $e; // minta retry queue dengan backoff
            }
        }
    }
}

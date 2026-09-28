<?php

namespace App\Listeners;

use App\Events\DeliveryCompleted;
use App\Events\GoodsReceived;
use App\Events\InspectionFailed;
use App\Events\ProductionCompleted;
use App\Events\QcFailed;
use App\Events\RecallCreated;
use App\Services\WebhookService;

class DispatchEventWebhooks
{
    public function __construct(protected WebhookService $webhooks) {}

    public function handle(object $event): void
    {
        [$name, $payload, $orgId] = match (true) {
            $event instanceof GoodsReceived => ['goods.received', ['number' => $event->receipt->number, 'warehouse_id' => $event->receipt->warehouse_id], $event->receipt->organization_id],
            $event instanceof ProductionCompleted => ['production.completed', ['number' => $event->order->number, 'produced_qty' => (float) $event->order->produced_qty], $event->order->organization_id],
            $event instanceof QcFailed => ['qc.failed', ['number' => $event->qc->number, 'result' => $event->qc->result], $event->qc->organization_id],
            $event instanceof InspectionFailed => ['qc.failed', ['number' => $event->inspection->number, 'result' => 'FAILED'], $event->inspection->organization_id],
            $event instanceof DeliveryCompleted => ['delivery.completed', ['number' => $event->delivery->number, 'status' => $event->delivery->status], $event->delivery->organization_id],
            $event instanceof RecallCreated => ['recall.created', ['number' => $event->recall->number, 'reason' => $event->recall->reason], $event->recall->organization_id],
            default => [null, [], null],
        };
        if ($name) {
            $this->webhooks->dispatch($name, $payload, $orgId);
        }
    }
}

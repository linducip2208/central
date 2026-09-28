<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    public const EVENTS = [
        'stock.updated', 'purchase.created', 'purchase.approved', 'goods.received',
        'production.created', 'production.completed', 'qc.failed', 'delivery.dispatched',
        'delivery.completed', 'recall.created',
    ];

    protected $fillable = ['organization_id', 'name', 'url', 'events', 'secret', 'is_active'];

    protected function casts(): array
    {
        return ['events' => 'array', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $w) {
            if (empty($w->secret)) {
                $w->secret = bin2hex(random_bytes(24));
            }
        });
    }

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function subscribes(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $fillable = ['webhook_id', 'event', 'payload', 'attempts', 'status_code', 'status', 'last_error', 'delivered_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'delivered_at' => 'datetime'];
    }

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }

    public function canRetry(): bool
    {
        return $this->status !== 'DELIVERED' && $this->attempts < 5;
    }
}

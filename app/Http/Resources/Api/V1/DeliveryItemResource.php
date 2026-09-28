<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'product' => $this->product->name ?? null,
            'qty_planned' => (int) $this->qty_planned,
            'qty_delivered' => (int) $this->qty_delivered,
            'qty_returned' => (int) $this->qty_returned,
        ];
    }
}

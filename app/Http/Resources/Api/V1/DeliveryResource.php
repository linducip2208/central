<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'delivery_date' => $this->delivery_date,
            'school' => $this->whenLoaded('school', fn () => ['id' => $this->school->id, 'name' => $this->school->name]),
            'qty' => ['planned' => (int) $this->qty_planned, 'delivered' => (int) $this->qty_delivered, 'returned' => (int) $this->qty_returned],
            'status' => $this->status,
            'eta' => $this->eta,
            'actual_arrival' => $this->actual_arrival,
            'late' => (bool) $this->isLate(),
            'items' => DeliveryItemResource::collection($this->whenLoaded('items')),
            'trackings' => $this->whenLoaded('trackings'),
        ];
    }
}

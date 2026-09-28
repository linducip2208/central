<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $qty = (float) $this->qty;
        $reserved = (float) $this->reserved;

        return [
            'item_type' => $this->item_type,
            'item_id' => $this->item_id,
            'name' => $this->resource['name'] ?? null,
            'qty' => $qty,
            'reserved' => $reserved,
            'available' => max(0, $qty - $reserved),
        ];
    }
}

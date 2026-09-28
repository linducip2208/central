<?php

namespace App\Events;

use App\Models\ProductionOrder;
use Illuminate\Foundation\Events\Dispatchable;

class ProductionCompleted
{
    use Dispatchable;

    public function __construct(public readonly ProductionOrder $order) {}
}

<?php

namespace App\Events;

use App\Models\GoodsReceipt;
use Illuminate\Foundation\Events\Dispatchable;

class GoodsReceived
{
    use Dispatchable;

    public function __construct(public readonly GoodsReceipt $receipt) {}
}

<?php

namespace App\Events;

use App\Models\Recall;
use Illuminate\Foundation\Events\Dispatchable;

class RecallCreated
{
    use Dispatchable;

    public function __construct(public readonly Recall $recall) {}
}

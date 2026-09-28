<?php

namespace App\Events;

use App\Models\QualityControl;
use Illuminate\Foundation\Events\Dispatchable;

class QcFailed
{
    use Dispatchable;

    public function __construct(public readonly QualityControl $qc) {}
}

<?php

namespace App\Events;

use App\Models\QualityInspection;
use Illuminate\Foundation\Events\Dispatchable;

class InspectionFailed
{
    use Dispatchable;

    public function __construct(public readonly QualityInspection $inspection) {}
}

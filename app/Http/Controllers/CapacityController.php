<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Models\CentralKitchen;
use App\Services\CapacityService;
use Illuminate\Http\Request;

class CapacityController extends Controller
{
    use AuthorizesOrgAccess;

    public function index(Request $request, CapacityService $capacity)
    {
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();
        $kitchenId = $request->get('central_kitchen_id', $request->user()->central_kitchen_id ?? $kitchens->first()?->id);
        $from = $request->get('from', now()->toDateString());
        $to = $request->get('to', now()->addDays(13)->toDateString());
        $days = $kitchenId ? $capacity->dailyLoad((int) $kitchenId, $from, $to) : [];

        return view('capacity.index', compact('kitchens', 'kitchenId', 'from', 'to', 'days'));
    }
}

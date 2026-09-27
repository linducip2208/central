<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Costing;
use Illuminate\Http\Request;

class CostingController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Costing::with(['productionOrder', 'menu'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $costings = $query->latest('costing_date')->paginate(20)->withQueryString();
        $summary = [
            'total' => (clone $query)->sum('total_cost'),
            'avg_per_portion' => (clone $query)->avg('cost_per_portion'),
        ];

        return view('costings.index', compact('costings', 'summary'));
    }

    public function show(Costing $costing)
    {
        $this->ensureOrgAccess($costing);
        $costing->load(['productionOrder.product', 'menu']);

        return view('costings.show', compact('costing'));
    }
}

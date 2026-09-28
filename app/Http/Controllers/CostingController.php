<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Costing;
use App\Models\Ingredient;
use App\Services\CostingService;
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

    public function show(Costing $costing, CostingService $costingService)
    {
        $this->ensureOrgAccess($costing);
        $costing->load(['productionOrder.product', 'menu']);
        $standard = null;
        $variance = null;
        if ($costing->productionOrder) {
            $standard = $costingService->standardForProduct($costing->productionOrder->product_id, max(1, (int) $costing->portions));
            $variance = [
                'material' => (float) $costing->material_cost - (float) $standard['material'],
                'per_portion' => (float) $costing->cost_per_portion - ($costing->portions > 0 ? (float) $standard['material'] / (int) $costing->portions : 0),
            ];
        }

        return view('costings.show', compact('costing', 'standard', 'variance'));
    }

    /** Riwayat harga beli per bahan. */
    public function history(Request $request, CostingService $costingService)
    {
        $ingredients = Ingredient::where('organization_id', $request->user()->organization_id)->orderBy('name')->get();
        $history = [];
        $selected = null;
        if ($request->filled('ingredient_id')) {
            $selected = Ingredient::findOrFail($request->ingredient_id);
            $history = $costingService->purchaseHistory((int) $selected->id);
        }

        return view('costings.history', compact('ingredients', 'history', 'selected'));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\DemandPlan;
use App\Models\Ingredient;
use App\Models\MrpRun;
use App\Services\BomService;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function demandPlans(Request $request)
    {
        $plans = DemandPlan::where('organization_id', $request->user()->organization_id)
            ->when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))
            ->latest()->paginate(20);

        return response()->json($plans);
    }

    public function mrpRuns(Request $request)
    {
        $runs = MrpRun::with('warehouse')->where('organization_id', $request->user()->organization_id)
            ->latest()->paginate(20);

        return response()->json($runs);
    }

    public function mrpShow(Request $request, MrpRun $run)
    {
        abort_unless((int) $run->organization_id === (int) $request->user()->organization_id, 403);
        $run->load(['lines.ingredient']);

        return response()->json($run);
    }

    public function bomExplode(Request $request, BomService $bom)
    {
        $request->validate(['product_id' => 'required|integer', 'qty' => 'required|numeric|min:0.01']);
        try {
            $needs = $bom->explode((int) $request->product_id, (float) $request->qty);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        $names = Ingredient::whereIn('id', collect($needs)->pluck('ingredient_id'))->pluck('name', 'id');

        return response()->json([
            'data' => array_map(fn ($n) => $n + ['name' => $names[$n['ingredient_id']] ?? null], $needs),
        ]);
    }

    public function boms(Request $request)
    {
        $boms = Bom::where('organization_id', $request->user()->organization_id)
            ->where('status', 'ACTIVE')->with('items')->paginate(20);

        return response()->json($boms);
    }
}

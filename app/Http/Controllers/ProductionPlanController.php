<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Menu;
use App\Models\ProductionOrder;
use App\Models\ProductionPlan;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionPlanController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = ProductionPlan::with(['menu'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $plans = $this->tableQuery($request, $query, ['number']);

        return view('production-plans.index', compact('plans'));
    }

    public function create(Request $request)
    {
        $menus = Menu::where('organization_id', $request->user()->organization_id)->where('status', 'APPROVED')->with('items.product')->latest()->take(50)->get();
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('production-plans.form', ['plan' => new ProductionPlan, 'menus' => $menus, 'kitchens' => $kitchens]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'menu_id' => 'nullable|exists:menus,id',
            'plan_date' => 'required|date',
            'target_portions' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);
        $plan = ProductionPlan::create([
            'organization_id' => $request->user()->organization_id,
            'central_kitchen_id' => $data['central_kitchen_id'],
            'menu_id' => $data['menu_id'] ?? null,
            'number' => $numbers->next('PP'),
            'plan_date' => $data['plan_date'],
            'target_portions' => $data['target_portions'],
            'status' => 'DRAFT',
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);
        if ($plan->menu_id) {
            $menu = Menu::with('items')->find($plan->menu_id);
            foreach ($menu->items as $mi) {
                $plan->items()->create(['product_id' => $mi->product_id, 'planned_qty' => (int) round((float) $mi->qty_per_portion * $plan->target_portions)]);
            }
        }

        return redirect()->route('production-plans.show', $plan)->with('success', 'Rencana produksi dibuat.');
    }

    public function show(ProductionPlan $plan)
    {
        $this->ensureOrgAccess($plan);
        $plan->load(['items.product', 'menu', 'orders']);

        return view('production-plans.show', compact('plan'));
    }

    public function approve(Request $request, ProductionPlan $plan)
    {
        $this->ensureOrgAccess($plan);
        abort_unless($plan->status === 'DRAFT', 422);
        $plan->update(['status' => 'APPROVED', 'approved_by' => $request->user()->id, 'approved_at' => now()]);

        return back()->with('success', 'Rencana disetujui.');
    }

    /** Generate production orders per produk dari plan yang sudah APPROVED. */
    public function generateOrders(Request $request, ProductionPlan $plan, NumberService $numbers)
    {
        $this->ensureOrgAccess($plan);
        abort_unless($plan->status === 'APPROVED', 422, 'Plan harus APPROVED.');
        $count = DB::transaction(function () use ($request, $plan, $numbers) {
            $n = 0;
            foreach ($plan->items as $item) {
                if (ProductionOrder::where('production_plan_id', $plan->id)->where('product_id', $item->product_id)->exists()) {
                    continue;
                }
                $product = $item->product;
                $recipe = $product->activeRecipe;
                $order = ProductionOrder::create([
                    'organization_id' => $plan->organization_id,
                    'central_kitchen_id' => $plan->central_kitchen_id,
                    'production_plan_id' => $plan->id,
                    'menu_id' => $plan->menu_id,
                    'product_id' => $item->product_id,
                    'recipe_id' => $recipe?->id,
                    'number' => $numbers->next('WO'),
                    'production_date' => $plan->plan_date,
                    'planned_qty' => $item->planned_qty,
                    'unit_id' => $product->unit_id,
                    'status' => 'PLANNED',
                    'created_by' => $request->user()->id,
                ]);
                if ($recipe) {
                    foreach ($recipe->items as $ri) {
                        $order->items()->create([
                            'ingredient_id' => $ri->ingredient_id,
                            'qty_required' => $ri->requiredFor((float) $item->planned_qty, (float) $recipe->yield_qty),
                            'unit_id' => $ri->unit_id,
                        ]);
                    }
                }
                $n++;
            }
            $plan->update(['status' => 'RELEASED']);

            return $n;
        });

        return back()->with('success', "Berhasil membuat {$count} production order.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Demand;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\PurchaseRequest;
use App\Models\School;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemandController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Demand::with(['school', 'menu'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $demands = $this->tableQuery($request, $query, ['code']);

        return view('demands.index', compact('demands'));
    }

    public function create(Request $request)
    {
        $schools = School::active()->where('organization_id', $request->user()->organization_id)->get();
        $menus = Menu::where('organization_id', $request->user()->organization_id)->latest()->take(50)->get();
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('demands.form', ['demand' => new Demand, 'schools' => $schools, 'menus' => $menus, 'kitchens' => $kitchens]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'school_id' => 'nullable|exists:schools,id',
            'menu_id' => 'nullable|exists:menus,id',
            'demand_date' => 'required|date',
            'portions' => 'required|integer|min:1',
            'source' => 'required|in:SCHOOL,FORECAST,MANUAL',
            'notes' => 'nullable|string',
        ]);
        $this->ensureKitchen((int) $data['central_kitchen_id']);
        if (! empty($data['school_id'])) {
            $this->ensureSchool((int) $data['school_id']);
        }
        if (! empty($data['menu_id'])) {
            $this->ensureOrgAccess(Menu::findOrFail($data['menu_id']));
        }
        $data['organization_id'] = $request->user()->organization_id;
        $data['code'] = $numbers->next('DM');
        $data['status'] = 'DRAFT';
        $data['created_by'] = $request->user()->id;
        if (! empty($data['menu_id'])) {
            $menu = Menu::with('items')->find($data['menu_id']);
            $data['qty'] = $menu ? $menu->items->sum(fn ($i) => (float) $i->qty_per_portion * (int) $data['portions']) : 0;
        }
        $demand = Demand::create($data);

        return redirect()->route('demands.index')->with('success', 'Demand '.$demand->code.' tersimpan.');
    }

    /** Agregasi demand menjadi draft Purchase Request (explosion via resep). */
    public function generatePr(Request $request, NumberService $numbers)
    {
        $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'from' => 'required|date', 'to' => 'required|date|after_or_equal:from',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);
        $this->ensureKitchen((int) $request->central_kitchen_id);
        $this->ensureWarehouse((int) $request->warehouse_id);

        $demands = Demand::with(['menu.items.product.activeRecipe.items'])
            ->where('organization_id', $request->user()->organization_id)
            ->where('central_kitchen_id', $request->validated()['central_kitchen_id'])
            ->whereBetween('demand_date', [$request->from, $request->to])
            ->where('status', 'DRAFT')
            ->get();

        if ($demands->isEmpty()) {
            return back()->with('error', 'Tidak ada demand DRAFT pada rentang tanggal tersebut.');
        }

        $needs = [];
        foreach ($demands as $d) {
            if (! $d->menu) {
                continue;
            }
            foreach ($d->menu->items as $mi) {
                $portions = (int) $d->portions * (float) $mi->qty_per_portion;
                $recipe = $mi->product->activeRecipe;
                if (! $recipe) {
                    continue;
                }
                foreach ($recipe->items as $ri) {
                    $qty = $ri->requiredFor($portions, (float) $recipe->yield_qty);
                    $key = $ri->ingredient_id;
                    $needs[$key] = ($needs[$key] ?? 0) + $qty;
                }
            }
        }

        if (empty($needs)) {
            return back()->with('error', 'Tidak ada kebutuhan bahan yang bisa dihitung (resep belum lengkap).');
        }

        $pr = DB::transaction(function () use ($request, $numbers, $needs, $demands) {
            $pr = PurchaseRequest::create([
                'organization_id' => $request->user()->organization_id,
                'central_kitchen_id' => $request->central_kitchen_id,
                'warehouse_id' => $request->warehouse_id,
                'number' => $numbers->next('PR'),
                'request_date' => now()->toDateString(),
                'needed_date' => $request->to,
                'status' => 'DRAFT',
                'notes' => 'Dibangkitkan dari '.$demands->count().' demand.',
                'requested_by' => $request->user()->id,
            ]);
            foreach ($needs as $ingId => $qty) {
                $ing = Ingredient::find($ingId);
                $pr->items()->create([
                    'ingredient_id' => $ingId, 'qty_requested' => round($qty, 3),
                    'unit_id' => $ing->unit_id, 'estimated_price' => $ing->standard_price,
                ]);
            }
            $demands->each->update(['status' => 'PLANNED']);

            return $pr;
        });

        return redirect()->route('purchase-requests.show', $pr)->with('success', 'Draft PR '.$pr->number.' dibuat dari agregasi demand.');
    }

    public function destroy(Demand $demand)
    {
        $this->ensureOrgAccess($demand);
        if ($demand->status !== 'DRAFT') {
            return back()->with('error', 'Hanya demand DRAFT yang dapat dihapus.');
        }
        $demand->delete();

        return back()->with('success', 'Demand dihapus.');
    }
}

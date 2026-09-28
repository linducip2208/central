<?php

namespace App\Http\Controllers;

use App\Core\Services\ApprovalService;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Menu;
use App\Models\MenuCycle;
use App\Models\Product;
use App\Services\NumberService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Menu::with(['centralKitchen'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $menus = $this->tableQuery($request, $query, ['name', 'code']);

        return view('menus.index', compact('menus'));
    }

    public function create(Request $request)
    {
        $products = Product::active()->where('organization_id', $request->user()->organization_id)->get();
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('menus.form', ['menu' => new Menu, 'products' => $products, 'kitchens' => $kitchens]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'nullable|exists:central_kitchens,id',
            'name' => 'required|string|max:255',
            'menu_date' => 'required|date',
            'meal_type' => 'required|in:BREAKFAST,LUNCH,SNACK',
            'planned_portions' => 'required|integer|min:1',
            'budget_per_portion' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:0.01',
        ]);
        $data['organization_id'] = $request->user()->organization_id;
        $data['code'] = 'MNU-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $data['status'] = 'DRAFT';
        $menu = Menu::create(collect($data)->except('products')->toArray());
        foreach ($data['products'] as $i => $p) {
            $menu->items()->create(['product_id' => $p['id'], 'qty_per_portion' => $p['qty'], 'sort_order' => $i]);
        }

        return redirect()->route('menus.show', $menu)->with('success', 'Menu berhasil dibuat.');
    }

    public function show(Menu $menu)
    {
        $this->ensureOrgAccess($menu);
        $menu->load(['items.product.recipes', 'products', 'nutrition']);

        return view('menus.show', compact('menu'));
    }

    public function updateStatus(Request $request, Menu $menu)
    {
        $this->ensureOrgAccess($menu);
        $request->validate(['status' => 'required|in:DRAFT,APPROVED,CANCELLED']);
        $menu->update(['status' => $request->status]);

        return back()->with('success', 'Status menu: '.$request->status);
    }

    public function storeNutrition(Request $request, Menu $menu)
    {
        $this->ensureOrgAccess($menu);
        $data = $request->validate([
            'calories' => 'nullable|numeric|min:0', 'protein_g' => 'nullable|numeric|min:0',
            'carbs_g' => 'nullable|numeric|min:0', 'fat_g' => 'nullable|numeric|min:0',
            'fiber_g' => 'nullable|numeric|min:0', 'sugar_g' => 'nullable|numeric|min:0',
            'sodium_mg' => 'nullable|numeric|min:0', 'serving_size_g' => 'nullable|numeric|min:0',
        ]);
        $menu->nutrition()->create($data + ['source' => 'MANUAL']);

        return back()->with('success', 'Data gizi tersimpan.');
    }

    public function destroy(Menu $menu)
    {
        $this->ensureOrgAccess($menu);
        if ($menu->status === 'APPROVED') {
            return back()->with('error', 'Menu yang sudah disetujui tidak dapat dihapus.');
        }
        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu dihapus.');
    }

    public function cycles(Request $request)
    {
        $cycles = MenuCycle::where('organization_id', $request->user()->organization_id)->with('days.menu')->get();

        return view('menus.cycles', compact('cycles'));
    }

    public function storeCycle(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'cycle_days' => 'required|integer|min:1|max:31',
            'start_date' => 'required|date',
        ]);
        MenuCycle::create($data + [
            'organization_id' => $request->user()->organization_id,
            'code' => $numbers->next('MCY'), 'status' => 'DRAFT',
        ]);

        return back()->with('success', 'Siklus menu dibuat. Tetapkan menu per hari.');
    }

    public function cycleShow(MenuCycle $cycle)
    {
        $this->ensureOrgAccess($cycle);
        $cycle->load(['days.menu']);
        $menus = Menu::where('organization_id', $cycle->organization_id)->where('status', 'APPROVED')->latest()->take(60)->get();

        return view('menus.cycle-show', compact('cycle', 'menus'));
    }

    public function storeCycleDay(Request $request, MenuCycle $cycle)
    {
        $this->ensureOrgAccess($cycle);
        $this->ensureOrgAccess(Menu::findOrFail($request->get('menu_id')));
        $data = $request->validate([
            'day_no' => 'required|integer|min:1|max:'.$cycle->cycle_days,
            'menu_id' => 'required|exists:menus,id',
        ]);
        $cycle->days()->updateOrCreate(['day_no' => $data['day_no']], $data);

        return back()->with('success', 'Menu hari ke-'.$data['day_no'].' ditetapkan.');
    }

    public function approveCycle(MenuCycle $cycle)
    {
        $this->ensureOrgAccess($cycle);
        abort_unless($cycle->days()->count() >= $cycle->cycle_days, 422, 'Lengkapi semua hari dalam siklus.');
        $cycle->update(['status' => 'APPROVED']);
        app(ApprovalService::class)->decide($cycle, 'APPROVE');

        return back()->with('success', 'Siklus menu disetujui.');
    }
}

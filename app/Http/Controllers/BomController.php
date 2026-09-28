<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Bom;
use App\Models\BomItem;
use App\Models\Ingredient;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Unit;
use App\Services\ApprovalService;
use App\Services\BomService;
use App\Services\NumberService;
use Illuminate\Http\Request;

class BomController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $boms = $this->tableQuery($request, Bom::with(['items'])->where('organization_id', $request->user()->organization_id), ['code']);
        if ($request->filled('status')) {
            $boms = $this->tableQuery($request, Bom::where('organization_id', $request->user()->organization_id)->where('status', $request->status), ['code']);
        }

        return view('boms.index', compact('boms'));
    }

    public function create(Request $request)
    {
        $products = Product::active()->where('organization_id', $request->user()->organization_id)->get();
        $ingredients = Ingredient::active()->where('organization_id', $request->user()->organization_id)->with('unit')->get();
        $units = Unit::where('is_active', true)->get();

        return view('boms.form', ['bom' => new Bom, 'products' => $products, 'ingredients' => $ingredients, 'units' => $units]);
    }

    public function store(Request $request, NumberService $numbers, BomService $bomService)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'version' => 'nullable|string|max:10',
            'effective_from' => 'nullable|date',
            'yield_qty' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.component_type' => 'required|in:ingredient,product,material',
            'items.*.component_id' => 'required|integer|min:1',
            'items.*.qty' => 'required|numeric|min:0.0001',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.scrap_pct' => 'nullable|numeric|min:0|max:100',
            'items.*.waste_pct' => 'nullable|numeric|min:0|max:100',
        ]);
        $bom = Bom::create([
            'organization_id' => $request->user()->organization_id,
            'item_type' => 'product', 'item_id' => $data['product_id'],
            'code' => $numbers->next('BOM'), 'version' => $data['version'] ?? '1.0',
            'effective_from' => $data['effective_from'] ?? now()->toDateString(),
            'yield_qty' => $data['yield_qty'], 'status' => 'DRAFT',
            'notes' => $data['notes'] ?? null, 'created_by' => $request->user()->id,
        ]);
        try {
            foreach ($data['items'] as $i => $it) {
                $bomService->assertNoCycle($bom, $it['component_type'], (int) $it['component_id']);
                $bom->items()->create([
                    'component_type' => $it['component_type'], 'component_id' => $it['component_id'],
                    'qty' => $it['qty'], 'unit_id' => $it['unit_id'],
                    'scrap_pct' => $it['scrap_pct'] ?? 0, 'waste_pct' => $it['waste_pct'] ?? 0,
                    'level' => 1, 'sort_order' => $i,
                ]);
            }
        } catch (\Throwable $e) {
            $bom->delete();

            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('boms.show', $bom)->with('success', 'BOM '.$bom->code.' dibuat.');
    }

    public function show(Bom $bom, BomService $bomService)
    {
        $this->ensureOrgAccess($bom);
        $bom->load(['items.unit']);
        $explosion = [];
        if ($bom->status === 'ACTIVE') {
            try {
                $explosion = $bomService->explode((int) $bom->item_id, (float) $bom->yield_qty);
            } catch (\Throwable $e) {
                $explosion = ['error' => $e->getMessage()];
            }
        }
        $names = Ingredient::whereIn('id', collect($explosion)->pluck('ingredient_id')->filter())->pluck('name', 'id');

        return view('boms.show', compact('bom', 'explosion', 'names'));
    }

    public function approve(Request $request, Bom $bom, ApprovalService $approvals)
    {
        $this->ensureOrgAccess($bom);
        abort_unless($bom->status === 'DRAFT', 422);
        // Arsipkan versi aktif lain untuk produk yang sama (versioning preserves history).
        Bom::where('item_type', $bom->item_type)->where('item_id', $bom->item_id)
            ->where('status', 'ACTIVE')->where('id', '!=', $bom->id)
            ->update(['status' => 'ARCHIVED']);
        $bom->update(['status' => 'ACTIVE', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
        $approvals->decide($bom, 'APPROVE');

        return back()->with('success', 'BOM diaktifkan. Versi lama diarsipkan (histori tetap tersimpan).');
    }

    public function destroy(Bom $bom)
    {
        $this->ensureOrgAccess($bom);
        abort_unless($bom->status === 'DRAFT', 422, 'Hanya BOM DRAFT yang dapat dihapus.');
        $bom->delete();

        return redirect()->route('boms.index')->with('success', 'BOM dihapus.');
    }

    /** Reverse BOM: BOM apa saja yang memakai ingredient ini. */
    public function usedIn(Request $request, Ingredient $ingredient, BomService $bomService)
    {
        $rows = BomItem::with(['bom'])
            ->where('component_type', 'ingredient')
            ->where('component_id', $ingredient->id)
            ->whereHas('bom', fn ($q) => $q->where('organization_id', $request->user()->organization_id))
            ->get();

        return view('boms.used-in', compact('ingredient', 'rows'));
    }

    /** Clone sebagai revisi DRAFT baru (versi +0.1). */
    public function clone(Bom $bom)
    {
        $this->ensureOrgAccess($bom);
        $copy = $bom->replicate(['code']);
        $copy->code = app(NumberService::class)->next('BOM');
        $copy->version = $this->bumpVersion($bom->version);
        $copy->status = 'DRAFT';
        $copy->approved_by = null;
        $copy->approved_at = null;
        $copy->save();
        foreach ($bom->items as $item) {
            $copy->items()->create($item->only(['parent_bom_item_id', 'component_type', 'component_id', 'qty', 'unit_id', 'scrap_pct', 'waste_pct', 'level', 'sort_order', 'notes']));
        }

        return redirect()->route('boms.show', $copy)->with('success', "BOM di-clone sebagai revisi {$copy->version}.");
    }

    protected function bumpVersion(string $version): string
    {
        $parts = explode('.', $version);
        $parts[count($parts) - 1] = ((int) end($parts)) + 1;

        return implode('.', $parts);
    }

    /** Bandingkan dua BOM + simulasi kebutuhan + cek ketersediaan (tanpa transaksi). */
    public function compare(Request $request, BomService $bomService)
    {
        $boms = Bom::where('organization_id', $request->user()->organization_id)->latest()->take(50)->get();
        if (! $request->filled(['a_id', 'b_id'])) {
            return view('boms.compare', compact('boms'));
        }
        $request->validate(['a_id' => 'required|exists:boms,id', 'b_id' => 'required|exists:boms,id|different:a_id', 'qty' => 'nullable|numeric|min:0.01']);
        $a = Bom::with(['items'])->findOrFail($request->a_id);
        $b = Bom::with(['items'])->findOrFail($request->b_id);
        $this->ensureOrgAccess($a);
        $this->ensureOrgAccess($b);
        $qty = (float) ($request->qty ?? 100);
        $expA = $this->safeExplode($bomService, (int) $a->item_id, $qty);
        $expB = $this->safeExplode($bomService, (int) $b->item_id, $qty);

        // Simulasi ketersediaan: bandingkan kebutuhan vs stok semua gudang org.
        $simulate = [];
        foreach (['A' => $expA, 'B' => $expB] as $label => $needs) {
            foreach ($needs as $need) {
                $ing = Ingredient::find($need['ingredient_id']);
                if (! $ing) {
                    continue;
                }
                $stock = InventoryStock::where('item_type', 'ingredient')->where('item_id', $ing->id)
                    ->whereHas('warehouse.centralKitchen', fn ($q) => $q->where('organization_id', $a->organization_id))->sum('qty');
                $simulate[$label][$ing->id] = ['name' => $ing->name, 'need' => $need['qty'], 'stock' => (float) $stock, 'short' => max(0, $need['qty'] - (float) $stock)];
            }
        }

        return view('boms.compare', compact('a', 'b', 'qty', 'expA', 'expB', 'simulate', 'boms'));
    }

    protected function safeExplode(BomService $bomService, int $productId, float $qty): array
    {
        try {
            return $bomService->explode($productId, $qty);
        } catch (\Throwable) {
            return [];
        }
    }
}

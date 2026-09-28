<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Delivery;
use App\Models\DeliveryReturn;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Waste;
use App\Services\InventoryService;
use App\Services\NumberService;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = DeliveryReturn::with(['delivery.school', 'product'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $returns = $this->tableQuery($request, $query, ['notes']);

        return view('returns.index', compact('returns'));
    }

    public function create(Request $request, Delivery $delivery)
    {
        $this->ensureOrgAccess($delivery);
        $delivery->load(['items.product', 'school']);
        $warehouses = Warehouse::where('central_kitchen_id', $delivery->central_kitchen_id)->get();

        return view('returns.form', compact('delivery', 'warehouses'));
    }

    public function store(Request $request, Delivery $delivery, InventoryService $inventory, PeriodService $periods)
    {
        $this->ensureOrgAccess($delivery);
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.reason' => 'required|in:DAMAGED,WRONG_MENU,EXCESS,REFUSED,OTHER',
            'items.*.condition' => 'required|in:GOOD,DAMAGED,EXPIRED',
            'notes' => 'nullable|string',
        ]);
        $this->ensureWarehouse((int) $data['warehouse_id']);
        $periods->assertOpen($delivery->organization_id, $delivery->central_kitchen_id, now()->toDateString());

        DB::transaction(function () use ($request, $delivery, $data) {
            foreach ($data['items'] as $it) {
                $product = Product::findOrFail($it['product_id']);
                $this->ensureOrgAccess($product);
                DeliveryReturn::create([
                    'organization_id' => $delivery->organization_id,
                    'central_kitchen_id' => $delivery->central_kitchen_id,
                    'delivery_id' => $delivery->id,
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $product->id,
                    'qty' => $it['qty'],
                    'reason' => $it['reason'],
                    'condition' => $it['condition'],
                    'disposition' => 'PENDING',
                    'status' => 'RECEIVED',
                    'notes' => $data['notes'] ?? null,
                    'received_by' => $request->user()->id,
                ]);
            }
            $delivery->increment('qty_returned', array_sum(array_column($data['items'], 'qty')));
        });

        return redirect()->route('returns.index')->with('success', 'Retur diterima. Tentukan disposisi: restock (baik) atau waste (rusak).');
    }

    /** GOOD → masuk stok kembali (movement RETURN). */
    public function restock(Request $request, DeliveryReturn $ret, InventoryService $inventory, NumberService $numbers, PeriodService $periods)
    {
        $this->ensureOrgAccess($ret);
        $periods->assertOpen($ret->organization_id, $ret->central_kitchen_id, now()->toDateString());
        abort_unless($ret->disposition === 'PENDING', 422);
        abort_unless($ret->condition === 'GOOD', 422, 'Hanya kondisi GOOD yang dapat di-restock.');
        try {
            $batch = $inventory->restock([
                'organization_id' => $ret->organization_id,
                'warehouse_id' => $ret->warehouse_id,
                'item_type' => 'product',
                'item_id' => $ret->product_id,
                'qty' => $ret->qty,
                'unit_cost' => 0,
                'reference_type' => DeliveryReturn::class,
                'reference_id' => $ret->id,
                'reference_no' => 'RTN-'.$ret->id,
                'notes' => 'Retur baik dari '.$ret->delivery->number,
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        $ret->update(['disposition' => 'RESTOCKED', 'batch_id' => $batch->id, 'status' => 'COMPLETED']);

        return back()->with('success', 'Retur masuk stok kembali sebagai '.$batch->batch_no.'.');
    }

    /** DAMAGED/EXPIRED → catat waste (tanpa menambah stok). */
    public function waste(Request $request, DeliveryReturn $ret, NumberService $numbers)
    {
        $this->ensureOrgAccess($ret);
        abort_unless($ret->disposition === 'PENDING', 422);
        $waste = Waste::create([
            'organization_id' => $ret->organization_id,
            'central_kitchen_id' => $ret->central_kitchen_id,
            'warehouse_id' => $ret->warehouse_id,
            'number' => $numbers->next('WST'),
            'waste_date' => now()->toDateString(),
            'item_type' => 'product',
            'item_id' => $ret->product_id,
            'qty' => $ret->qty,
            'unit_id' => $ret->product->unit_id,
            'reason' => $ret->condition === 'EXPIRED' ? 'EXPIRED' : 'SPOILED',
            'notes' => 'Retur '.$ret->delivery->number.': '.$ret->reason,
            'reported_by' => $request->user()->id,
        ]);
        $ret->update(['disposition' => 'WASTED', 'status' => 'COMPLETED']);

        return back()->with('success', 'Retur dicatat sebagai waste '.$waste->number.'.');
    }
}

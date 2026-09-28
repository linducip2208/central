<?php

namespace App\Http\Controllers;

use App\Core\Services\NotificationService;
use App\Events\GoodsReceived;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['supplier', 'purchaseOrder'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $grs = $this->tableQuery($request, $query, ['number', 'delivery_note_no']);

        return view('goods-receipts.index', compact('grs'));
    }

    public function create(Request $request)
    {
        $pos = PurchaseOrder::where('organization_id', $request->user()->organization_id)
            ->whereIn('status', ['APPROVED', 'PARTIAL'])
            ->with(['items.ingredient.unit', 'warehouse'])
            ->latest()->take(50)->get();
        $warehouses = Warehouse::whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $request->user()->organization_id))->get();

        return view('goods-receipts.form', ['gr' => new GoodsReceipt, 'pos' => $pos, 'warehouses' => $warehouses]);
    }

    /**
     * Posting penerimaan: validasi sisa PO → ledger receive per item → update qty_received → status PO.
     * Idempotent: double-submit dengan nomor sama ditolak via unique + duplicate guard di service.
     */
    public function store(Request $request, NumberService $numbers, InventoryService $inventory)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_note_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.rejected' => 'nullable|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.batch_no' => 'nullable|string|max:50',
        ]);

        $po = PurchaseOrder::with('items')->findOrFail($data['purchase_order_id']);
        $this->ensureOrgAccess($po);
        $this->ensureWarehouse((int) $data['warehouse_id']);
        abort_unless(in_array($po->status, ['APPROVED', 'PARTIAL']), 422, 'PO belum disetujui.');

        try {
            $gr = DB::transaction(function () use ($request, $data, $numbers, $inventory, $po) {
                $gr = GoodsReceipt::create([
                    'organization_id' => $request->user()->organization_id,
                    'central_kitchen_id' => $po->central_kitchen_id,
                    'warehouse_id' => $data['warehouse_id'],
                    'purchase_order_id' => $po->id,
                    'supplier_id' => $po->supplier_id,
                    'number' => $numbers->next('GR'),
                    'receipt_date' => now()->toDateString(),
                    'delivery_note_no' => $data['delivery_note_no'] ?? null,
                    'status' => 'RECEIVED',
                    'notes' => $data['notes'] ?? null,
                    'received_by' => $request->user()->id,
                ]);

                foreach ($data['items'] as $it) {
                    $poItem = $po->items->firstWhere('id', $it['po_item_id']);
                    abort_unless($poItem, 422, 'Item PO tidak valid.');
                    $qty = (float) $it['qty'];
                    abort_if($qty > $poItem->remainingToReceive() + 1e-9, 422, "Qty terima melebihi sisa PO untuk {$poItem->ingredient->name} (sisa {$poItem->remainingToReceive()}).");

                    $gr->items()->create([
                        'purchase_order_item_id' => $poItem->id,
                        'ingredient_id' => $poItem->ingredient_id,
                        'qty_ordered' => $poItem->qty_ordered,
                        'qty_received' => $qty,
                        'qty_rejected' => $it['rejected'] ?? 0,
                        'unit_id' => $poItem->unit_id,
                        'unit_price' => $poItem->unit_price,
                        'batch_no' => $it['batch_no'] ?? null,
                        'expiry_date' => $it['expiry_date'] ?? null,
                    ]);

                    $inventory->receive([
                        'organization_id' => $gr->organization_id,
                        'warehouse_id' => $gr->warehouse_id,
                        'item_type' => 'ingredient',
                        'item_id' => $poItem->ingredient_id,
                        'qty' => $qty,
                        'unit_id' => $poItem->unit_id,
                        'unit_cost' => (float) $poItem->unit_price,
                        'batch_no' => $it['batch_no'] ?? null,
                        'expiry_date' => $it['expiry_date'] ?? null,
                        'supplier_id' => $po->supplier_id,
                        'reference_type' => GoodsReceipt::class,
                        'reference_id' => $gr->id,
                        'reference_no' => $gr->number,
                    ]);

                    $poItem->increment('qty_received', $qty);
                }

                $po->refresh()->refreshReceiveStatus();

                return $gr;
            });
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $this->notifyLowStock($gr);
        event(new GoodsReceived($gr));

        return redirect()->route('goods-receipts.show', $gr)->with('success', 'Penerimaan '.$gr->number.' diposting ke stok.');
    }

    public function show(GoodsReceipt $gr)
    {
        $this->ensureOrgAccess($gr);
        $gr->load(['items.ingredient.unit', 'purchaseOrder', 'supplier', 'warehouse', 'receiver']);

        return view('goods-receipts.show', compact('gr'));
    }

    /** Notifikasi bila ada bahan yang masih di bawah minimum setelah penerimaan. */
    protected function notifyLowStock(GoodsReceipt $gr): void
    {
        $low = [];
        foreach ($gr->items as $item) {
            $ing = $item->ingredient;
            if (! $ing) {
                continue;
            }
            $stock = (float) InventoryStock::where('item_type', 'ingredient')
                ->where('item_id', $ing->id)
                ->whereHas('warehouse', fn ($q) => $q->where('central_kitchen_id', $gr->central_kitchen_id))
                ->sum('qty');
            if ($stock <= (float) $ing->min_stock) {
                $low[] = ['name' => $ing->name, 'code' => $ing->code, 'stock' => $stock, 'min' => (float) $ing->min_stock, 'unit' => $ing->unit->symbol ?? ''];
            }
        }
        if ($low) {
            app(NotificationService::class)->sendLowStockAlert($low);
        }
    }
}

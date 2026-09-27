<?php

namespace App\Http\Controllers;

use App\Core\Services\NotificationService;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $pos = $this->tableQuery($request, $query, ['number']);

        return view('purchase-orders.index', compact('pos'));
    }

    public function create(Request $request)
    {
        $prs = PurchaseRequest::where('organization_id', $request->user()->organization_id)->where('status', 'APPROVED')->with('items')->latest()->take(50)->get();
        $suppliers = Supplier::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('purchase-orders.form', ['po' => new PurchaseOrder, 'prs' => $prs, 'suppliers' => $suppliers]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'expected_date' => 'nullable|date',
            'payment_terms' => 'required|in:CASH,CREDIT,COD',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.pr_item_id' => 'nullable|exists:purchase_request_items,id',
        ]);

        $po = DB::transaction(function () use ($request, $data, $numbers) {
            $pr = ! empty($data['purchase_request_id']) ? PurchaseRequest::find($data['purchase_request_id']) : null;
            $po = PurchaseOrder::create([
                'organization_id' => $request->user()->organization_id,
                'central_kitchen_id' => $pr?->central_kitchen_id ?? $request->user()->central_kitchen_id ?? CentralKitchen::first()->id,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $pr?->warehouse_id,
                'purchase_request_id' => $pr?->id,
                'number' => $numbers->next('PO'),
                'order_date' => now()->toDateString(),
                'expected_date' => $data['expected_date'] ?? null,
                'payment_terms' => $data['payment_terms'],
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null,
                'ordered_by' => $request->user()->id,
            ]);
            foreach ($data['items'] as $it) {
                $ing = Ingredient::find($it['ingredient_id']);
                $po->items()->create([
                    'purchase_request_item_id' => $it['pr_item_id'] ?? null,
                    'ingredient_id' => $ing->id, 'qty_ordered' => $it['qty'],
                    'unit_id' => $ing->unit_id, 'unit_price' => $it['price'],
                ]);
                if (! empty($it['pr_item_id'])) {
                    PurchaseRequestItem::whereKey($it['pr_item_id'])->increment('qty_ordered', $it['qty']);
                }
            }
            $po->recalculateTotals();
            if ($pr) {
                $pending = $pr->items()->whereRaw('qty_ordered < qty_approved')->exists();
                $pr->update(['status' => $pending ? 'PARTIAL' : 'ORDERED']);
            }

            return $po;
        });

        return redirect()->route('purchase-orders.show', $po)->with('success', 'PO '.$po->number.' dibuat.');
    }

    public function show(PurchaseOrder $po)
    {
        $this->ensureOrgAccess($po);
        $po->load(['items.ingredient.unit', 'supplier', 'receipts', 'purchaseRequest', 'warehouse']);

        return view('purchase-orders.show', compact('po'));
    }

    public function submit(PurchaseOrder $po)
    {
        $this->ensureOrgAccess($po);
        abort_unless($po->status === 'DRAFT', 422);
        $po->update(['status' => 'SUBMITTED']);

        return back()->with('success', 'PO disubmit.');
    }

    public function approve(PurchaseOrder $po)
    {
        $this->ensureOrgAccess($po);
        abort_unless($po->status === 'SUBMITTED', 422);
        $po->update(['status' => 'APPROVED', 'approved_by' => request()->user()->id, 'approved_at' => now()]);
        app(NotificationService::class)->sendPurchaseApproved($po->id);

        return back()->with('success', 'PO disetujui. Barang dapat diterima via Goods Receipt.');
    }

    public function reject(Request $request, PurchaseOrder $po)
    {
        $this->ensureOrgAccess($po);
        $request->validate(['reject_reason' => 'required|string']);
        abort_unless(in_array($po->status, ['DRAFT', 'SUBMITTED']), 422);
        $po->update(['status' => 'REJECTED', 'reject_reason' => $request->reject_reason]);

        return back()->with('success', 'PO ditolak.');
    }

    public function cancel(PurchaseOrder $po)
    {
        $this->ensureOrgAccess($po);
        abort_unless(in_array($po->status, ['DRAFT', 'SUBMITTED', 'APPROVED']), 422, 'PO yang sudah diterima sebagian tidak dapat dibatalkan.');
        abort_if($po->items()->where('qty_received', '>', 0)->exists(), 422, 'PO sudah ada penerimaan.');
        $po->update(['status' => 'CANCELLED']);

        return back()->with('success', 'PO dibatalkan.');
    }
}

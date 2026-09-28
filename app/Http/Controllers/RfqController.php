<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\Supplier;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RfqController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Rfq::where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $rfqs = $this->tableQuery($request, $query, ['number']);

        return view('rfqs.index', compact('rfqs'));
    }

    public function create(Request $request)
    {
        $prs = PurchaseRequest::where('organization_id', $request->user()->organization_id)->whereIn('status', ['APPROVED', 'PARTIAL'])->with('items')->latest()->take(30)->get();
        $suppliers = Supplier::active()->where('organization_id', $request->user()->organization_id)->get();
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('rfqs.form', ['rfq' => new Rfq, 'prs' => $prs, 'suppliers' => $suppliers, 'kitchens' => $kitchens]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'deadline' => 'nullable|date|after:today',
            'notes' => 'nullable|string',
            'supplier_ids' => 'required|array|min:1',
            'supplier_ids.*' => 'exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.qty' => 'required|numeric|min:0.001',
        ]);
        $this->ensureKitchen((int) $data['central_kitchen_id']);
        $supplierCount = Supplier::where('organization_id', $request->user()->organization_id)->whereIn('id', $data['supplier_ids'])->count();
        abort_unless($supplierCount === count($data['supplier_ids']), 403, 'Supplier di luar organisasi Anda.');
        $rfq = DB::transaction(function () use ($request, $data, $numbers) {
            $rfq = Rfq::create([
                'organization_id' => $request->user()->organization_id,
                'central_kitchen_id' => $data['central_kitchen_id'],
                'purchase_request_id' => $data['purchase_request_id'] ?? null,
                'number' => $numbers->next('RFQ'),
                'rfq_date' => now()->toDateString(),
                'deadline' => $data['deadline'] ?? null,
                'status' => 'SENT',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            foreach ($data['items'] as $it) {
                $ing = Ingredient::find($it['ingredient_id']);
                $rfq->items()->create(['ingredient_id' => $ing->id, 'qty' => $it['qty'], 'unit_id' => $ing->unit_id]);
            }
            $rfq->suppliers()->sync($data['supplier_ids']);

            return $rfq;
        });

        return redirect()->route('rfqs.show', $rfq)->with('success', 'RFQ dikirim ke '.count($data['supplier_ids']).' supplier.');
    }

    public function show(Rfq $rfq)
    {
        $this->ensureOrgAccess($rfq);
        $rfq->load(['items.ingredient.unit', 'suppliers', 'quotations.items', 'quotations.supplier']);

        return view('rfqs.show', compact('rfq'));
    }

    public function storeQuotation(Request $request, Rfq $rfq, NumberService $numbers)
    {
        $this->ensureOrgAccess($rfq);
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'lead_time_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'prices' => 'required|array',
            'prices.*' => 'nullable|numeric|min:0',
        ]);
        abort_unless($rfq->suppliers()->where('suppliers.id', $data['supplier_id'])->exists(), 422, 'Supplier tidak diundang dalam RFQ ini.');

        $quo = DB::transaction(function () use ($rfq, $data, $numbers) {
            $quo = Quotation::create([
                'rfq_id' => $rfq->id, 'supplier_id' => $data['supplier_id'],
                'number' => $numbers->next('QUO'), 'quotation_date' => now()->toDateString(),
                'lead_time_days' => $data['lead_time_days'] ?? 0, 'status' => 'SUBMITTED',
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($rfq->items as $item) {
                $price = $data['prices'][$item->id] ?? null;
                if ($price === null) {
                    continue;
                }
                $quo->items()->create(['rfq_item_id' => $item->id, 'unit_price' => $price, 'qty_offered' => $item->qty]);
            }
            abort_unless($quo->items()->exists(), 422, 'Minimal satu harga harus diisi.');

            return $quo;
        });

        return back()->with('success', 'Penawaran '.$quo->number.' tercatat.');
    }

    /** Pilih pemenang per item (termurah yang memenuhi qty) → buat PO. */
    public function award(Request $request, Rfq $rfq, NumberService $numbers)
    {
        $this->ensureOrgAccess($rfq);
        abort_unless(in_array($rfq->status, ['SENT', 'DRAFT']), 422, 'RFQ sudah di-award.');
        abort_unless($rfq->quotations()->where('status', 'SUBMITTED')->exists(), 422, 'Belum ada penawaran.');

        $po = DB::transaction(function () use ($request, $rfq, $numbers) {
            // Kelompokkan pemenang per supplier.
            $winners = []; // supplier_id => [rfq_item_id => quotation_item]
            foreach ($rfq->items as $item) {
                $best = null;
                foreach ($rfq->quotations()->where('status', 'SUBMITTED')->with('items')->get() as $quo) {
                    $qi = $quo->items->firstWhere('rfq_item_id', $item->id);
                    if (! $qi || (float) $qi->qty_offered < (float) $item->qty) {
                        continue;
                    }
                    if (! $best || (float) $qi->unit_price < (float) $best->unit_price) {
                        $best = $qi;
                    }
                }
                if ($best) {
                    $winners[$best->quotation->supplier_id][$item->id] = $best;
                }
            }
            abort_unless($winners, 422, 'Tidak ada penawaran yang memenuhi qty.');
            $firstSupplier = array_key_first($winners);
            $po = PurchaseOrder::create([
                'organization_id' => $rfq->organization_id,
                'central_kitchen_id' => $rfq->central_kitchen_id,
                'supplier_id' => $firstSupplier,
                'purchase_request_id' => $rfq->purchase_request_id,
                'number' => $numbers->next('PO'),
                'order_date' => now()->toDateString(),
                'status' => 'DRAFT',
                'payment_terms' => 'CREDIT',
                'notes' => 'Dari RFQ '.$rfq->number,
                'ordered_by' => $request->user()->id,
            ]);
            foreach ($winners[$firstSupplier] as $itemId => $qi) {
                $item = $rfq->items->firstWhere('id', $itemId);
                $po->items()->create([
                    'ingredient_id' => $item->ingredient_id, 'qty_ordered' => $item->qty,
                    'unit_id' => $item->unit_id, 'unit_price' => $qi->unit_price,
                ]);
                Quotation::where('id', $qi->quotation_id)->update(['status' => 'SELECTED']);
            }
            $po->recalculateTotals();
            $rfq->update(['status' => 'AWARDED']);

            return $po;
        });

        return redirect()->route('purchase-orders.show', $po)->with('success', 'Pemenang dipilih. PO '.$po->number.' dibuat (supplier pertama; pecah manual bila multi-supplier).');
    }
}

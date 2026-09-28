<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Services\ApprovalService;
use App\Services\AutomationService;
use App\Services\NumberService;
use App\Services\WebhookService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierInvoiceController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = SupplierInvoice::with(['supplier'])->where('organization_id', $request->user()->organization_id);
        if ($request->filled('match')) {
            $query->where('match_status', $request->match);
        }
        $invoices = $this->tableQuery($request, $query, ['number', 'supplier_invoice_no']);

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $pos = PurchaseOrder::where('organization_id', $request->user()->organization_id)->whereIn('status', ['APPROVED', 'PARTIAL', 'COMPLETED'])->with(['items.ingredient', 'supplier'])->latest()->take(30)->get();
        $suppliers = Supplier::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('invoices.form', ['invoice' => new SupplierInvoice, 'pos' => $pos, 'suppliers' => $suppliers]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_invoice_no' => 'required|string|max:60',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.qty' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        $this->ensureOrgAccess(Supplier::findOrFail($data['supplier_id']));
        if (! empty($data['purchase_order_id'])) {
            $this->ensureOrgAccess(PurchaseOrder::findOrFail($data['purchase_order_id']));
        }
        foreach ($data['items'] as $it) {
            $this->ensureOrgAccess(Ingredient::findOrFail($it['ingredient_id']));
        }

        try {
            $invoice = DB::transaction(function () use ($request, $data, $numbers) {
                $po = ! empty($data['purchase_order_id']) ? PurchaseOrder::find($data['purchase_order_id']) : null;
                $gr = $po ? GoodsReceipt::where('purchase_order_id', $po->id)->latest()->first() : null;
                $invoice = SupplierInvoice::create([
                    'organization_id' => $request->user()->organization_id,
                    'supplier_id' => $data['supplier_id'],
                    'purchase_order_id' => $po?->id,
                    'goods_receipt_id' => $gr?->id,
                    'supplier_invoice_no' => $data['supplier_invoice_no'],
                    'number' => $numbers->next('INV'),
                    'invoice_date' => $data['invoice_date'],
                    'due_date' => $data['due_date'] ?? null,
                    'tax_amount' => $data['tax_amount'] ?? 0,
                    'status' => 'DRAFT',
                    'notes' => $data['notes'] ?? null,
                ]);
                $subtotal = 0;
                foreach ($data['items'] as $it) {
                    $line = $invoice->items()->create(['ingredient_id' => $it['ingredient_id'], 'qty' => $it['qty'], 'unit_price' => $it['price']]);
                    $subtotal += (float) $line->line_total;
                }
                $invoice->update(['subtotal' => $subtotal, 'grand_total' => $subtotal + (float) $invoice->tax_amount]);
                $this->threeWayMatch($invoice);
                if ($invoice->fresh()->match_status === 'VARIANCE') {
                    app(AutomationService::class)->fire('invoice.variance', ['organization_id' => $invoice->organization_id, 'number' => $invoice->number, 'message' => "Invoice {$invoice->number} variansi (3-way match)."]);
                }

                return $invoice;
            });
        } catch (UniqueConstraintViolationException $e) {
            return back()->withInput()->with('error', 'Nomor invoice supplier ini sudah pernah dicatat (duplikat ditolak).');
        }

        app(WebhookService::class)->dispatch('invoice.created', ['number' => $invoice->number, 'total' => (float) $invoice->grand_total], $invoice->organization_id);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice dicatat + 3-way matching dijalankan.');
    }

    public function show(SupplierInvoice $invoice)
    {
        $this->ensureOrgAccess($invoice);
        $invoice->load(['items.ingredient', 'supplier', 'purchaseOrder.items', 'goodsReceipt.items']);

        return view('invoices.show', compact('invoice'));
    }

    public function verify(Request $request, SupplierInvoice $invoice, ApprovalService $approvals)
    {
        $this->ensureOrgAccess($invoice);
        abort_unless($invoice->status === 'DRAFT', 422);
        abort_unless($approvals->canDecide($request->user(), $invoice, (float) $invoice->grand_total), 403, 'Tidak memenuhi matriks persetujuan untuk nominal ini.');
        $this->threeWayMatch($invoice->fresh());
        $invoice->refresh();
        abort_unless($invoice->isMatched(), 422, 'Masih ada variansi — selesaikan selisih sebelum verifikasi.');
        $invoice->update(['status' => 'VERIFIED', 'verified_by' => $request->user()->id]);
        $level = $approvals->requiredRole($invoice->organization_id, 'SupplierInvoice', (float) $invoice->grand_total)['level'] ?? 1;
        $approvals->decide($invoice, 'APPROVE', 'Verifikasi 3-way match', $level);
        $invoice->purchaseOrder?->update(['invoice_status' => 'BILLED']);

        return back()->with('success', 'Invoice terverifikasi.');
    }

    public function markPaid(SupplierInvoice $invoice)
    {
        $this->ensureOrgAccess($invoice);
        abort_unless($invoice->status === 'VERIFIED', 422, 'Hanya invoice terverifikasi yang dapat dibayar.');
        $invoice->update(['payment_status' => 'PAID']);

        return back()->with('success', 'Invoice ditandai lunas.');
    }

    /**
     * 3-way matching: PO vs GR vs Invoice.
     * qty_variance = billed − received; price_variance = Σ billed − Σ PO.
     */
    public function threeWayMatch(SupplierInvoice $invoice): void
    {
        $po = $invoice->purchaseOrder;
        $gr = $invoice->goodsReceipt;
        $qtyVar = 0;
        $priceVar = 0;
        foreach ($invoice->items as $item) {
            $poQty = $po ? (float) $po->items()->where('ingredient_id', $item->ingredient_id)->sum('qty_ordered') : 0;
            $poPrice = $po ? (float) $po->items()->where('ingredient_id', $item->ingredient_id)->value('unit_price') : 0;
            $grQty = $gr ? (float) $gr->items()->where('ingredient_id', $item->ingredient_id)->sum('qty_received') : $poQty;
            $qtyVar += (float) $item->qty - $grQty;
            $priceVar += ((float) $item->qty * (float) $item->unit_price) - ($grQty * $poPrice);
        }
        $ppn = (float) setting('tax.ppn_pct', 11);
        $expectedTax = round($invoice->subtotal * $ppn / 100, 2);
        // Pajak 0 = belum termasuk pajak (bukan variansi); variansi hanya bila tagihan mencantumkan pajak yang menyimpang.
        $taxVar = (float) $invoice->tax_amount > 0 ? (float) $invoice->tax_amount - $expectedTax : 0;
        $matched = abs($qtyVar) < 0.001 && abs($priceVar) < 0.01 && abs($taxVar) < 1;
        $invoice->update([
            'qty_variance' => $qtyVar, 'price_variance' => $priceVar,
            'match_status' => $matched ? 'MATCHED' : 'VARIANCE',
            'notes' => trim(($invoice->notes ? $invoice->notes.' | ' : '').(abs($taxVar) >= 1 ? 'Pajak tagih '.mbg_currency((float) $invoice->tax_amount)." vs ekspektasi PPN {$ppn}% = ".mbg_currency($expectedTax) : '')) ?: null,
        ]);
    }
}

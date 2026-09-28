<?php

namespace App\Http\Controllers;

use App\Core\Services\NotificationService;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Ingredient;
use App\Models\PurchaseRequest;
use App\Models\Warehouse;
use App\Services\ApprovalService;
use App\Services\NumberService;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['warehouse'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $prs = $this->tableQuery($request, $query, ['number']);

        return view('purchase-requests.index', compact('prs'));
    }

    public function create(Request $request)
    {
        $ingredients = Ingredient::active()->where('organization_id', $request->user()->organization_id)->with('unit')->get();
        $warehouses = Warehouse::whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $request->user()->organization_id))->get();
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('purchase-requests.form', ['pr' => new PurchaseRequest, 'ingredients' => $ingredients, 'warehouses' => $warehouses, 'kitchens' => $kitchens]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'needed_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.qty' => 'required|numeric|min:0.001',
        ]);
        $this->ensureKitchen((int) $data['central_kitchen_id']);
        if (! empty($data['warehouse_id'])) {
            $this->ensureWarehouse((int) $data['warehouse_id']);
        }
        foreach ($data['items'] as $it) {
            $this->ensureOrgAccess(Ingredient::findOrFail($it['ingredient_id']));
        }
        $pr = PurchaseRequest::create([
            'organization_id' => $request->user()->organization_id,
            'central_kitchen_id' => $data['central_kitchen_id'],
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'number' => $numbers->next('PR'),
            'request_date' => now()->toDateString(),
            'needed_date' => $data['needed_date'] ?? null,
            'status' => 'DRAFT',
            'notes' => $data['notes'] ?? null,
            'requested_by' => $request->user()->id,
        ]);
        foreach ($data['items'] as $it) {
            $ing = Ingredient::find($it['ingredient_id']);
            $pr->items()->create(['ingredient_id' => $ing->id, 'qty_requested' => $it['qty'], 'unit_id' => $ing->unit_id, 'estimated_price' => $ing->standard_price]);
        }

        return redirect()->route('purchase-requests.show', $pr)->with('success', 'PR '.$pr->number.' dibuat.');
    }

    public function show(PurchaseRequest $pr)
    {
        $this->ensureOrgAccess($pr);
        $pr->load(['items.ingredient.unit', 'warehouse', 'requester', 'approver', 'centralKitchen']);

        return view('purchase-requests.show', compact('pr'));
    }

    public function submit(PurchaseRequest $pr)
    {
        $this->ensureOrgAccess($pr);
        abort_unless($pr->status === 'DRAFT' && $pr->items()->exists(), 422, 'PR draft tanpa item tidak dapat disubmit.');
        $pr->update(['status' => 'SUBMITTED']);

        return back()->with('success', 'PR disubmit untuk persetujuan.');
    }

    public function approve(Request $request, PurchaseRequest $pr)
    {
        $this->ensureOrgAccess($pr);
        abort_unless($pr->status === 'SUBMITTED', 422);
        $items = $request->validate(['approved' => 'required|array', 'approved.*' => 'numeric|min:0']);
        foreach ($pr->items as $item) {
            $item->update(['qty_approved' => $items['approved'][$item->id] ?? $item->qty_requested]);
        }
        $pr->update(['status' => 'APPROVED', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
        app(ApprovalService::class)->decide($pr, 'APPROVE');
        if ($pr->requester) {
            app(NotificationService::class)->send([$pr->requester], 'pr_approved', [
                'number' => $pr->number, 'by' => $request->user()->name,
            ]);
        }

        return back()->with('success', 'PR disetujui.');
    }

    public function reject(Request $request, PurchaseRequest $pr)
    {
        $this->ensureOrgAccess($pr);
        $request->validate(['reject_reason' => 'required|string']);
        abort_unless(in_array($pr->status, ['SUBMITTED', 'DRAFT']), 422);
        $pr->update(['status' => 'REJECTED', 'reject_reason' => $request->reject_reason]);
        app(ApprovalService::class)->decide($pr, 'REJECT', $request->reject_reason);
        if ($pr->requester) {
            app(NotificationService::class)->send([$pr->requester], 'pr_rejected', [
                'number' => $pr->number, 'reason' => $request->reject_reason,
            ]);
        }

        return back()->with('success', 'PR ditolak.');
    }

    public function destroy(PurchaseRequest $pr)
    {
        $this->ensureOrgAccess($pr);
        abort_unless($pr->isEditable(), 422, 'Hanya PR DRAFT/REJECTED yang dapat dihapus.');
        $pr->delete();

        return redirect()->route('purchase-requests.index')->with('success', 'PR dihapus.');
    }
}

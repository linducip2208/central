<?php

namespace App\Http\Controllers;

use App\Core\Services\NotificationService;
use App\Events\RecallCreated;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Batch;
use App\Models\InventoryMovement;
use App\Models\Recall;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\NumberService;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecallController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Recall::where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $recalls = $this->tableQuery($request, $query, ['number']);

        return view('recalls.index', compact('recalls'));
    }

    public function create(Request $request)
    {
        $batches = Batch::where('organization_id', $request->user()->organization_id)
            ->whereIn('status', ['AVAILABLE', 'BLOCKED'])
            ->latest()->take(50)->get();

        return view('recalls.form', ['recall' => new Recall, 'batches' => $batches]);
    }

    public function store(Request $request, NumberService $numbers, TraceabilityService $trace)
    {
        $data = $request->validate([
            'trigger_batch_id' => 'required|exists:batches,id',
            'reason' => 'required|in:CONTAMINATION,ALLERGEN,FOREIGN_OBJECT,SPOILAGE,OTHER',
            'severity' => 'required|in:CLASS_I,CLASS_II,CLASS_III',
            'description' => 'required|string|min:10',
        ]);
        $trigger = Batch::findOrFail($data['trigger_batch_id']);
        $this->ensureOrgAccess($trigger);

        $recall = DB::transaction(function () use ($request, $data, $numbers, $trace, $trigger) {
            $recall = Recall::create([
                'organization_id' => $trigger->organization_id,
                'central_kitchen_id' => $trigger->warehouse->central_kitchen_id,
                'trigger_batch_id' => $trigger->id,
                'number' => $numbers->next('RCL'),
                'reason' => $data['reason'], 'severity' => $data['severity'],
                'description' => $data['description'], 'status' => 'DRAFT',
                'created_by' => $request->user()->id,
            ]);
            // Kumpulkan semua batch terdampak dari genealogy forward.
            $affected = [$trigger->id];
            $forward = $trace->forward($trigger);
            foreach ($forward['productions'] as $prod) {
                foreach ($prod['finished_batches'] as $fb) {
                    $affected[] = $fb['id'];
                }
            }
            foreach (array_unique($affected) as $batchId) {
                $b = Batch::find($batchId);
                if (! $b) {
                    continue;
                }
                $delivered = (float) InventoryMovement::where('batch_id', $b->id)->where('movement_type', 'DELIVERY')->sum('qty');
                $recall->items()->create([
                    'batch_id' => $b->id,
                    'stock_on_hand' => (float) $b->remaining_qty,
                    'qty_delivered' => $delivered,
                    'action' => 'QUARANTINE',
                ]);
            }

            return $recall;
        });
        event(new RecallCreated($recall));

        return redirect()->route('recalls.show', $recall)->with('success', 'Recall dibuat dengan '.$recall->items()->count().' batch terdampak. Aktifkan untuk karantina.');
    }

    public function show(Recall $recall, TraceabilityService $trace)
    {
        $this->ensureOrgAccess($recall);
        $recall->load(['items.batch.warehouse', 'triggerBatch']);
        $forward = $trace->forward($recall->triggerBatch);

        return view('recalls.show', compact('recall', 'forward'));
    }

    /** Aktifkan: karantina semua batch terdampak yang masih tersedia + notifikasi. */
    public function activate(Request $request, Recall $recall, ApprovalService $approvals)
    {
        $this->ensureOrgAccess($recall);
        abort_unless($recall->status === 'DRAFT', 422);
        DB::transaction(function () use ($request, $recall, $approvals) {
            foreach ($recall->items as $item) {
                $batch = $item->batch;
                if ($batch && $batch->status === 'AVAILABLE') {
                    $batch->update(['status' => 'BLOCKED', 'hold_reason' => 'RECALL '.$recall->number]);
                }
            }
            $recall->update(['status' => 'ACTIVE', 'approved_by' => $request->user()->id, 'approved_at' => now()]);
            $approvals->decide($recall, 'APPROVE', 'Recall activated', 2);
        });
        $admins = User::where('organization_id', $recall->organization_id)->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))->get();
        app(NotificationService::class)->send($admins, 'recall_created', ['number' => $recall->number, 'reason' => $recall->reason]);

        return back()->with('success', 'Recall aktif. Batch terdampak dikarantina.');
    }

    public function contain(Request $request, Recall $recall)
    {
        $this->ensureOrgAccess($recall);
        $request->validate(['actions_taken' => 'required|string']);
        abort_unless($recall->status === 'ACTIVE', 422);
        $recall->update(['status' => 'CONTAINED', 'actions_taken' => $request->actions_taken]);

        return back()->with('success', 'Recall ditandai terkendali.');
    }

    public function close(Recall $recall)
    {
        $this->ensureOrgAccess($recall);
        abort_unless($recall->status === 'CONTAINED', 422);
        $recall->update(['status' => 'CLOSED']);

        return back()->with('success', 'Recall ditutup.');
    }
}

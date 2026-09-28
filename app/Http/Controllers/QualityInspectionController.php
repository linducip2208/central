<?php

namespace App\Http\Controllers;

use App\Core\Services\ApprovalService;
use App\Core\Services\NotificationService;
use App\Events\InspectionFailed;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Batch;
use App\Models\CapaAction;
use App\Models\CentralKitchen;
use App\Models\GoodsReceipt;
use App\Models\InspectionTemplate;
use App\Models\NonConformance;
use App\Models\ProductionOrder;
use App\Models\QualityInspection;
use App\Models\TemperatureLog;
use App\Models\User;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QualityInspectionController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function templates(Request $request)
    {
        $templates = InspectionTemplate::where('organization_id', $request->user()->organization_id)->paginate(15);

        return view('qms.templates', compact('templates'));
    }

    public function storeTemplate(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'stage' => 'required|in:INCOMING,IN_PROCESS,FINISHED',
            'parameters' => 'required|array|min:1',
            'parameters.*.name' => 'required|string',
            'parameters.*.spec_min' => 'nullable|numeric',
            'parameters.*.spec_max' => 'nullable|numeric',
            'parameters.*.unit' => 'nullable|string|max:20',
        ]);
        InspectionTemplate::create([
            'organization_id' => $request->user()->organization_id,
            'code' => $numbers->next('QCT'),
            'name' => $data['name'], 'stage' => $data['stage'],
            'parameters' => array_values($data['parameters']), 'is_active' => true,
        ]);

        return back()->with('success', 'Template inspeksi dibuat.');
    }

    public function index(Request $request)
    {
        $query = QualityInspection::with(['template'])->where('organization_id', $request->user()->organization_id);
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }
        $inspections = $this->tableQuery($request, $query, ['number']);

        return view('qms.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $templates = InspectionTemplate::active()->where('organization_id', $request->user()->organization_id)->get();
        $orders = ProductionOrder::where('organization_id', $request->user()->organization_id)->whereIn('status', ['IN_PROGRESS', 'PARTIAL', 'COMPLETED'])->latest()->take(20)->get();
        $receipts = GoodsReceipt::where('organization_id', $request->user()->organization_id)->latest()->take(20)->get();

        return view('qms.form', ['inspection' => new QualityInspection, 'templates' => $templates, 'orders' => $orders, 'receipts' => $receipts]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'inspection_template_id' => 'nullable|exists:inspection_templates,id',
            'reference_kind' => 'required|in:production,receipt',
            'reference_id' => 'required|integer|min:1',
            'temperature_c' => 'nullable|numeric',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'measured' => 'required|array',
            'notes' => 'nullable|string',
        ]);
        $refType = $data['reference_kind'] === 'production' ? ProductionOrder::class : GoodsReceipt::class;
        $ref = $refType::findOrFail($data['reference_id']);
        $this->ensureOrgAccess($ref);
        $template = ! empty($data['inspection_template_id']) ? InspectionTemplate::find($data['inspection_template_id']) : null;
        if ($template) {
            $this->ensureOrgAccess($template);
        }

        $results = [];
        $failed = 0;
        foreach ($template?->parameters ?? [] as $i => $param) {
            $measured = $data['measured'][$i] ?? $data['measured'][$param['name']] ?? null;
            $pass = true;
            if ($measured === null || $measured === '') {
                $pass = false;
            } else {
                if (isset($param['spec_min']) && $param['spec_min'] !== null && (float) $measured < (float) $param['spec_min']) {
                    $pass = false;
                }
                if (isset($param['spec_max']) && $param['spec_max'] !== null && (float) $measured > (float) $param['spec_max']) {
                    $pass = false;
                }
            }
            if (! $pass) {
                $failed++;
            }
            $results[] = ['parameter' => $param['name'], 'measured' => $measured, 'pass' => $pass];
        }
        $result = $failed > 0 ? 'FAILED' : 'PASSED';

        $inspection = DB::transaction(function () use ($request, $data, $numbers, $refType, $ref, $template, $results, $result) {
            $photo = $request->hasFile('photo') ? $request->file('photo')->store('qc-photos', 'public') : null;

            return QualityInspection::create([
                'organization_id' => $request->user()->organization_id,
                'central_kitchen_id' => $ref->central_kitchen_id ?? $request->user()->central_kitchen_id,
                'inspection_template_id' => $template?->id,
                'reference_type' => $refType, 'reference_id' => $ref->id,
                'number' => $numbers->next('QCI'),
                'inspection_date' => now()->toDateString(),
                'results' => $results, 'temperature_c' => $data['temperature_c'] ?? null,
                'photo_path' => $photo, 'result' => $result,
                'notes' => $data['notes'] ?? null, 'inspected_by' => $request->user()->id,
            ]);
        });

        if ($result === 'FAILED') {
            event(new InspectionFailed($inspection));
            $admins = User::where('organization_id', $inspection->organization_id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))
                ->get();
            app(NotificationService::class)->send($admins, 'qc_alert', [
                'number' => $inspection->number, 'result' => 'FAILED',
                'reference' => class_basename($refType).' #'.$ref->id,
            ]);
        }

        return redirect()->route('inspections.index')->with($result === 'FAILED' ? 'error' : 'success', "Inspeksi {$inspection->number}: {$result}");
    }

    public function show(QualityInspection $inspection)
    {
        $this->ensureOrgAccess($inspection);
        $inspection->load(['template', 'inspector', 'nonConformances']);

        return view('qms.show', compact('inspection'));
    }

    /** Buat NCR dari inspeksi gagal + opsional karantina batch. */
    public function storeNcr(Request $request, QualityInspection $inspection, NumberService $numbers)
    {
        $this->ensureOrgAccess($inspection);
        $data = $request->validate([
            'batch_id' => 'nullable|exists:batches,id',
            'category' => 'required|in:MATERIAL,PROCESS,HYGIENE,EQUIPMENT,FOREIGN_OBJECT,OTHER',
            'severity' => 'required|in:MINOR,MAJOR,CRITICAL',
            'description' => 'required|string',
            'disposition' => 'required|in:HOLD,REWORK,REJECT,RELEASE',
        ]);

        $ncr = DB::transaction(function () use ($request, $inspection, $data, $numbers) {
            $ncr = NonConformance::create([
                'organization_id' => $inspection->organization_id,
                'central_kitchen_id' => $inspection->central_kitchen_id,
                'quality_inspection_id' => $inspection->id,
                'batch_id' => $data['batch_id'] ?? null,
                'number' => $numbers->next('NCR'),
                'category' => $data['category'], 'severity' => $data['severity'],
                'description' => $data['description'], 'disposition' => $data['disposition'],
                'status' => 'OPEN', 'reported_by' => $request->user()->id,
            ]);
            if ($ncr->batch_id && in_array($data['disposition'], ['HOLD', 'REJECT'])) {
                $batch = Batch::find($ncr->batch_id);
                if ($batch && $batch->status === 'AVAILABLE') {
                    $batch->update(['status' => 'BLOCKED', 'hold_reason' => 'NCR '.$ncr->number]);
                }
            }

            return $ncr;
        });

        return back()->with('success', 'NCR '.$ncr->number.' dibuat (batch dikarantina bila disposisi HOLD/REJECT).');
    }

    public function ncrs(Request $request)
    {
        $query = NonConformance::with(['batch'])->where('organization_id', $request->user()->organization_id);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $ncrs = $this->tableQuery($request, $query, ['number']);

        return view('qms.ncrs', compact('ncrs'));
    }

    public function ncrShow(NonConformance $ncr)
    {
        $this->ensureOrgAccess($ncr);
        $ncr->load(['batch', 'inspection', 'capaActions.owner']);

        return view('qms.ncr-show', compact('ncr'));
    }

    public function storeCapa(Request $request, NonConformance $ncr)
    {
        $this->ensureOrgAccess($ncr);
        $data = $request->validate([
            'action_type' => 'required|in:CORRECTIVE,PREVENTIVE',
            'action' => 'required|string',
            'owner_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);
        $ncr->capaActions()->create($data + ['status' => 'OPEN']);

        return back()->with('success', 'CAPA ditambahkan.');
    }

    public function completeCapa(CapaAction $capa)
    {
        $this->ensureOrgAccess($capa->nonConformance);
        $capa->update(['status' => 'DONE', 'completed_date' => now()->toDateString()]);
        $ncr = $capa->nonConformance;
        if ($ncr->capaActions()->where('status', '!=', 'DONE')->doesntExist()) {
            $ncr->update(['status' => 'CLOSED']);
        }

        return back()->with('success', 'CAPA selesai.');
    }

    public function closeNcr(NonConformance $ncr, ApprovalService $approvals)
    {
        $this->ensureOrgAccess($ncr);
        $ncr->update(['status' => 'CLOSED']);
        $approvals->decide($ncr, 'APPROVE', 'NCR closed');

        return back()->with('success', 'NCR ditutup.');
    }

    public function tempLogs(Request $request)
    {
        $query = TemperatureLog::where('organization_id', $request->user()->organization_id)->latest('logged_at');
        if ($request->filled('oor') && $request->boolean('oor')) {
            $query->where('in_spec', false);
        }
        $logs = $query->paginate(25)->withQueryString();
        $checkpoints = array_keys(TemperatureLog::SPECS);

        return view('qms.temp', compact('logs', 'checkpoints'));
    }

    public function storeTempLog(Request $request)
    {
        $data = $request->validate([
            'checkpoint' => 'required|string|max:60',
            'temperature_c' => 'required|numeric',
            'corrective_action' => 'nullable|string',
        ]);
        $log = TemperatureLog::create([
            'organization_id' => $request->user()->organization_id,
            'central_kitchen_id' => $request->user()->central_kitchen_id ?? CentralKitchen::first()->id,
            'checkpoint' => $data['checkpoint'],
            'temperature_c' => $data['temperature_c'],
            'logged_at' => now(),
            'corrective_action' => $data['corrective_action'] ?? null,
            'logged_by' => $request->user()->id,
        ]);

        return back()->with($log->in_spec ? 'success' : 'error', $log->in_spec ? 'Suhu dalam spesifikasi.' : 'PERINGATAN: suhu di luar spesifikasi — catat tindakan koreksi.');
    }
}

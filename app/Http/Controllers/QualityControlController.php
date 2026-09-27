<?php

namespace App\Http\Controllers;

use App\Core\Services\NotificationService;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\GoodsReceipt;
use App\Models\ProductionOrder;
use App\Models\QualityControl;
use App\Models\User;
use App\Services\NumberService;
use Illuminate\Http\Request;

class QualityControlController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = QualityControl::where('organization_id', $request->user()->organization_id);
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }
        $qcs = $this->tableQuery($request, $query, ['number']);

        return view('quality-controls.index', compact('qcs'));
    }

    public function create(Request $request)
    {
        $orders = ProductionOrder::where('organization_id', $request->user()->organization_id)->whereIn('status', ['PARTIAL', 'COMPLETED', 'IN_PROGRESS'])->latest()->take(30)->get();
        $receipts = GoodsReceipt::where('organization_id', $request->user()->organization_id)->latest()->take(30)->get();

        return view('quality-controls.form', ['qc' => new QualityControl, 'orders' => $orders, 'receipts' => $receipts]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'reference_kind' => 'required|in:production,receipt',
            'reference_id' => 'required|integer',
            'check_type' => 'required|in:ORGANOLEPTIC,MICROBIOLOGY,PHYSICAL,PACKAGING',
            'sample_qty' => 'required|numeric|min:0.001',
            'pass_qty' => 'required|numeric|min:0',
            'fail_qty' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $refType = $data['reference_kind'] === 'production' ? ProductionOrder::class : GoodsReceipt::class;
        $ref = $refType::findOrFail($data['reference_id']);
        $total = (float) $data['pass_qty'] + (float) $data['fail_qty'];
        abort_if(abs($total - (float) $data['sample_qty']) > 1e-6, 422, 'pass + fail harus sama dengan sample.');

        $result = (float) $data['fail_qty'] == 0 ? 'PASSED' : ((float) $data['pass_qty'] == 0 ? 'FAILED' : 'CONDITIONAL');
        $qc = QualityControl::create([
            'organization_id' => $request->user()->organization_id,
            'central_kitchen_id' => $ref->central_kitchen_id ?? $request->user()->central_kitchen_id,
            'reference_type' => $refType,
            'reference_id' => $ref->id,
            'number' => $numbers->next('QC'),
            'check_date' => now()->toDateString(),
            'check_type' => $data['check_type'],
            'sample_qty' => $data['sample_qty'], 'pass_qty' => $data['pass_qty'], 'fail_qty' => $data['fail_qty'],
            'result' => $result,
            'notes' => $data['notes'] ?? null,
            'checked_by' => $request->user()->id,
        ]);
        if (in_array($result, ['FAILED', 'CONDITIONAL'])) {
            $admins = User::where('organization_id', $qc->organization_id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))
                ->get();
            app(NotificationService::class)->send($admins, 'qc_alert', [
                'number' => $qc->number, 'result' => $result,
                'reference' => class_basename($refType).' #'.$ref->id,
            ]);
        }

        return redirect()->route('quality-controls.index')->with('success', "QC {$qc->number}: {$result}");
    }
}

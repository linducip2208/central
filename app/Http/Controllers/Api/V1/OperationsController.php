<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\QualityInspection;
use App\Models\Recall;
use App\Models\SupplierInvoice;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    protected function orgScope(Request $request, $query)
    {
        return $query->where('organization_id', $request->user()->organization_id);
    }

    protected function ensureOrg(Request $request, object $model): void
    {
        abort_unless((int) ($model->organization_id ?? 0) === (int) $request->user()->organization_id, 403);
    }

    public function productionOrders(Request $request)
    {
        return response()->json($this->orgScope($request, ProductionOrder::with('product'))->latest()->paginate(20));
    }

    public function productionShow(Request $request, ProductionOrder $order)
    {
        $this->ensureOrg($request, $order);
        $order->load(['items.ingredient', 'operators.user']);

        return response()->json($order);
    }

    public function inspections(Request $request)
    {
        return response()->json($this->orgScope($request, QualityInspection::query())->latest()->paginate(20));
    }

    public function recalls(Request $request)
    {
        return response()->json($this->orgScope($request, Recall::with('items'))->latest()->paginate(20));
    }

    public function recallShow(Request $request, Recall $recall)
    {
        $this->ensureOrg($request, $recall);
        $recall->load(['items.batch']);

        return response()->json($recall);
    }

    public function invoices(Request $request)
    {
        return response()->json($this->orgScope($request, SupplierInvoice::with('supplier'))->latest()->paginate(20));
    }
}

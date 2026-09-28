<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Batch;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();
        $query = Batch::with(['warehouse'])
            ->when($warehouses->isNotEmpty(), fn ($q) => $q->whereIn('warehouse_id', $warehouses->pluck('id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('expiring'), fn ($q) => $q->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', now()->addDays((int) $request->expiring)))
            ->when($request->filled('q'), fn ($q) => $q->where('batch_no', 'like', '%'.$request->q.'%'))
            ->latest();
        $batches = $query->paginate(20)->withQueryString();
        $batches->getCollection()->transform(function ($b) {
            $b->item_name = $b->item_type === 'product'
                ? Product::whereKey($b->item_id)->value('name')
                : Ingredient::whereKey($b->item_id)->value('name');

            return $b;
        });

        return view('batches.index', compact('batches', 'warehouses'));
    }

    public function block(Batch $batch)
    {
        abort_unless($batch->status === 'AVAILABLE', 422);
        $batch->update(['status' => 'BLOCKED']);

        return back()->with('success', 'Batch '.$batch->batch_no.' diblokir (tidak ikut FEFO).');
    }

    public function unblock(Batch $batch)
    {
        abort_unless($batch->status === 'BLOCKED', 422);
        $batch->update(['status' => 'AVAILABLE']);

        return back()->with('success', 'Batch dibuka kembali.');
    }
}

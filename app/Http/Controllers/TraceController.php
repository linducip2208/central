<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Models\Batch;
use App\Models\Ingredient;
use App\Models\Product;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;

class TraceController extends Controller
{
    use AuthorizesOrgAccess;

    public function form()
    {
        return view('trace.form');
    }

    public function lookup(Request $request, TraceabilityService $trace)
    {
        $request->validate(['batch_no' => 'required|string|max:60']);
        $batch = Batch::with(['warehouse'])->where('batch_no', $request->batch_no)->first();
        abort_if(! $batch, 404, 'Batch tidak ditemukan.');
        $this->ensureOrgAccess($batch);

        return redirect()->route('trace.batch', $batch);
    }

    public function batch(Batch $batch, TraceabilityService $trace)
    {
        $this->ensureOrgAccess($batch);
        $batch->load(['warehouse', 'supplier', 'bin']);
        $itemName = $batch->item_type === 'product'
            ? Product::whereKey($batch->item_id)->value('name')
            : Ingredient::whereKey($batch->item_id)->value('name');
        $forward = $trace->forward($batch);
        $backward = $trace->backward($batch);

        return view('trace.show', compact('batch', 'itemName', 'forward', 'backward'));
    }
}

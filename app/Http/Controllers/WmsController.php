<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Batch;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use Illuminate\Http\Request;

class WmsController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function locations(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->with(['zones.racks.bins'])->get();

        return view('wms.locations', compact('warehouses'));
    }

    public function storeZone(Request $request)
    {
        $data = $request->validate(['warehouse_id' => 'required|exists:warehouses,id', 'code' => 'required|string|max:20', 'name' => 'required|string|max:255', 'zone_type' => 'required|in:STORAGE,RECEIVING,QUARANTINE,DISPATCH']);
        WarehouseZone::create($data);

        return back()->with('success', 'Zona ditambahkan.');
    }

    public function storeRack(Request $request)
    {
        $data = $request->validate(['warehouse_zone_id' => 'required|exists:warehouse_zones,id', 'code' => 'required|string|max:20', 'name' => 'required|string|max:255']);
        WarehouseRack::create($data);

        return back()->with('success', 'Rak ditambahkan.');
    }

    public function storeBin(Request $request)
    {
        $data = $request->validate(['warehouse_rack_id' => 'required|exists:warehouse_racks,id', 'code' => 'required|string|max:30', 'barcode' => 'nullable|string|max:60|unique:warehouse_bins,barcode']);
        if (empty($data['barcode'])) {
            $rack = WarehouseRack::with('zone')->find($data['warehouse_rack_id']);
            $data['barcode'] = ($rack->zone->code ?? 'Z').'-'.$rack->code.'-'.$data['code'];
        }
        WarehouseBin::create($data + ['is_active' => true]);

        return back()->with('success', 'Bin ditambahkan.');
    }

    /** Put-away: tempatkan batch ke bin. */
    public function putaway(Request $request, Batch $batch)
    {
        $this->ensureOrgAccess($batch);
        $request->validate(['bin_id' => 'required|exists:warehouse_bins,id']);
        $bin = WarehouseBin::with('rack.zone')->findOrFail($request->bin_id);
        abort_unless((int) $bin->rack->zone->warehouse_id === (int) $batch->warehouse_id, 422, 'Bin beda gudang dengan batch.');
        $batch->update(['bin_id' => $bin->id]);

        return back()->with('success', 'Batch ditempatkan di '.$bin->fullCode().'.');
    }

    /** Quarantine / quality hold: blokir batch dari FEFO. */
    public function quarantine(Request $request, Batch $batch)
    {
        $this->ensureOrgAccess($batch);
        $request->validate(['hold_reason' => 'required|string|max:100']);
        abort_unless($batch->status === 'AVAILABLE', 422);
        $batch->update(['status' => 'BLOCKED', 'hold_reason' => $request->hold_reason]);

        return back()->with('success', 'Batch dikarantina: '.$request->hold_reason);
    }

    public function release(Request $request, Batch $batch)
    {
        $this->ensureOrgAccess($batch);
        abort_unless($batch->status === 'BLOCKED', 422);
        $batch->update(['status' => 'AVAILABLE', 'hold_reason' => null]);

        return back()->with('success', 'Batch dirilis dari karantina.');
    }

    /** Barcode/QR scan: lookup bin atau batch. Ramah untuk pemindai (autofocus + enter). */
    public function scan(Request $request)
    {
        $result = null;
        if ($request->filled('code')) {
            $code = trim($request->code);
            $bin = WarehouseBin::with(['rack.zone', 'batches'])->where('barcode', $code)->first();
            if ($bin) {
                $result = ['type' => 'bin', 'bin' => $bin];
            } else {
                $batch = Batch::with(['warehouse', 'bin'])->where('batch_no', $code)->first();
                $result = $batch ? ['type' => 'batch', 'batch' => $batch] : ['type' => 'none'];
            }
        }

        return view('wms.scan', compact('result'));
    }
}

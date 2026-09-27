@extends('layouts.app')
@section('title', 'WO ' . $order->number)
@section('subtitle', ($order->product->name ?? '-') . ' · ' . number_format($order->planned_qty, 0) . ' rencana · ' . number_format($order->produced_qty, 0) . ' hasil')
@section('actions')
@if($order->status === 'PLANNED')
<form method="POST" action="{{ route('production-orders.release', $order) }}" class="d-inline">@csrf<button class="btn btn-primary" type="submit">Rilis ke dapur</button></form>
<form method="POST" action="{{ route('production-orders.cancel', $order) }}" class="d-inline" onsubmit="return confirm('Batalkan order?')">@csrf<button class="btn btn-ghost-danger" type="submit">Batal</button></form>
@endif
@if(in_array($order->status, ['RELEASED','PARTIAL']))
<form method="POST" action="{{ route('production-orders.start', $order) }}" class="d-inline">@csrf<button class="btn btn-primary" type="submit">Mulai masak</button></form>
@endif
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Kebutuhan bahan <span class="ms-2"><x-badge :status="$order->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Butuh</th><th class="text-end">Terpakai</th><th class="text-end">Sisa</th></tr></thead>
<tbody>
@foreach($order->items as $it)
<tr><td>{{ $it->ingredient->name ?? '-' }}</td><td class="text-end">{{ number_format($it->qty_required, 2) }}</td><td class="text-end">{{ number_format($it->qty_consumed, 2) }}</td><td class="text-end fw-bold">{{ number_format($it->qty_required - $it->qty_consumed, 2) }}</td></tr>
@endforeach
</tbody></table></div></div>

@if(in_array($order->status, ['RELEASED','IN_PROGRESS','PARTIAL']))
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Konsumsi bahan (FEFO, boleh parsial)</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('production-orders.consume', $order) }}">@csrf
<div class="mb-2"><label class="form-label">Gudang asal</label>
<select name="warehouse_id" class="form-select">@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
@foreach($order->items as $it)
<div class="row g-2 mb-2">
<div class="col-7"><input class="form-control" value="{{ $it->ingredient->name }}" disabled/><input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $it->id }}"/></div>
<div class="col-5"><input name="items[{{ $loop->index }}][qty]" type="number" step="0.001" min="0" max="{{ max(0, $it->qty_required - $it->qty_consumed) }}" value="{{ max(0, $it->qty_required - $it->qty_consumed) }}" class="form-control"/></div>
</div>
@endforeach
<button class="btn btn-warning" type="submit">Catat konsumsi</button>
</form>
</div></div>

<div class="card mt-3"><div class="card-header"><h3 class="card-title">Selesaikan & catat hasil</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('production-orders.complete', $order) }}">@csrf
<div class="row g-2">
<div class="col-md-4"><label class="form-label">Gudang hasil</label>
<select name="warehouse_id" class="form-select">@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Hasil baik</label><input name="produced_qty" type="number" step="0.001" min="0" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Reject</label><input name="rejected_qty" type="number" step="0.001" min="0" value="0" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Expired hasil</label><input name="expiry_date" type="date" class="form-control" value="{{ now()->addDay()->toDateString() }}"/></div>
<div class="col-md-3"><label class="form-label">Biaya tenaga</label><input name="labor_cost" type="number" min="0" value="0" class="form-control"/></div>
<div class="col-md-3"><label class="form-label">Biaya overhead</label><input name="overhead_cost" type="number" min="0" value="0" class="form-control"/></div>
</div>
<button class="btn btn-success mt-2" type="submit">Selesai + masuk stok</button>
</form>
</div></div>
@endif
</div>
<div class="col-lg-5">
<div class="card"><div class="card-body">
<div class="progress mb-2"><div class="progress-bar bg-green" style="width: {{ min(100, $order->completionPct()) }}%"></div></div>
<p class="text-secondary">{{ $order->completionPct() }}% selesai · reject {{ number_format($order->rejected_qty, 0) }}</p>
<dl class="row small mb-0">
<dt class="col-5">Resep</dt><dd class="col-7">{{ $order->recipe->name ?? '—' }}</dd>
<dt class="col-5">Unit dapur</dt><dd class="col-7">{{ $order->kitchenUnit->name ?? '—' }}</dd>
<dt class="col-5">Mulai</dt><dd class="col-7">{{ $order->started_at ?? '—' }}</dd>
<dt class="col-5">Selesai</dt><dd class="col-7">{{ $order->completed_at ?? '—' }}</dd>
<dt class="col-5">Catatan</dt><dd class="col-7">{{ $order->notes ?? '—' }}</dd>
</dl>
</div></div>
</div>
</div>
@endsection

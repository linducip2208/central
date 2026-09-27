@extends('layouts.app')
@section('title', 'Laporan Stok')
@section('subtitle', 'Total nilai persediaan: ' . mbg_currency($totalValue))
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><select name="warehouse_id" class="form-select" onchange="this.form.submit()"><option value="">— Semua gudang —</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit" onclick="window.print()">Cetak</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Item</th><th>Batch</th><th class="text-end">Qty</th><th class="text-end">Avg cost</th><th class="text-end">Nilai</th></tr></thead>
<tbody>
@foreach($stocks as $s)
<tr><td class="text-secondary">{{ $s->warehouse->name ?? '' }}</td><td>{{ $s->item_name ?? '' }}</td><td class="text-secondary">{{ $s->batch->batch_no ?? '-' }}</td><td class="text-end">{{ number_format($s->qty, 2) }}</td><td class="text-end">{{ mbg_currency($s->avg_cost) }}</td><td class="text-end">{{ mbg_currency($s->stock_value) }}</td></tr>
@endforeach
<tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end">{{ mbg_currency($totalValue) }}</td></tr>
</tbody></table></div>
</div></div>
@endsection

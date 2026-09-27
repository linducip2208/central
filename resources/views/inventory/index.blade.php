@extends('layouts.app')
@section('title', 'Stok Inventory')
@section('actions')<a href="{{ route('inventory.adjust.form') }}" class="btn btn-white">Penyesuaian</a><a href="{{ route('inventory.transfer.form') }}" class="btn btn-white">Transfer</a><a href="{{ route('inventory.reserve.form') }}" class="btn btn-white">Reservasi</a><a href="{{ route('inventory.movements') }}" class="btn btn-white">Ledger mutasi</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><select name="warehouse_id" class="form-select" onchange="this.form.submit()"><option value="">— Semua gudang —</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-3"><select name="item_type" class="form-select" onchange="this.form.submit()"><option value="">— Bahan & produk —</option><option value="ingredient" @selected(request('item_type') === 'ingredient')>Bahan baku</option><option value="product" @selected(request('item_type') === 'product')>Produk jadi</option></select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Tipe</th><th>Item</th><th>Batch</th><th class="text-end">Qty</th><th class="text-end">Tertahan</th><th class="text-end">Tersedia</th><th class="text-end">Avg cost</th></tr></thead>
<tbody>
@forelse($stocks as $s)
<tr>
<td class="text-secondary">{{ $s->warehouse->name ?? '-' }}</td>
<td><span class="badge bg-blue-lt">{{ $s->item_type }}</span></td>
<td>{{ $s->item_name ?? $s->item_type.' #'.$s->item_id }}</td>
<td class="text-secondary">{{ $s->batch->batch_no ?? '-' }}</td>
<td class="text-end">{{ number_format($s->qty, 2) }}</td>
<td class="text-end text-secondary">{{ number_format($s->reserved_qty, 2) }}</td>
<td class="text-end fw-bold">{{ number_format($s->qty - $s->reserved_qty, 2) }}</td>
<td class="text-end">{{ mbg_currency($s->avg_cost) }}</td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Tidak ada stok"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $stocks->links() }}</div>
</div></div>
@endsection

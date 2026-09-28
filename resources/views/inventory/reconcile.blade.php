@extends('layouts.app')
@section('title', 'Rekonsiliasi Inventory')
@section('subtitle', 'Ledger vs agregat · stok vs batch · reservasi vs tersedia · valuasi')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><select name="warehouse_id" class="form-select" onchange="this.form.submit()"><option value="">— Semua gudang —</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>@endforeach</select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Item</th><th class="text-end">Ledger (IN−OUT)</th><th class="text-end">Agregat stok</th><th class="text-end">Batch aktif</th><th class="text-end">Reserved</th><th class="text-end">Nilai</th><th>Cek</th></tr></thead>
<tbody>
@forelse($checks as $c)
<tr>
<td class="text-secondary">{{ $c['warehouse'] }}</td>
<td>{{ $c['item'] }}</td>
<td class="text-end">{{ number_format($c['ledger'], 2) }}</td>
<td class="text-end">{{ number_format($c['stock'], 2) }}</td>
<td class="text-end">{{ number_format($c['batch'], 2) }}</td>
<td class="text-end">{{ number_format($c['reserved'], 2) }}</td>
<td class="text-end">{{ mbg_currency($c['value']) }}</td>
<td>@if($c['ok_ledger'] && $c['ok_batch'] && $c['ok_reserved'])<span class="badge bg-green-lt">OK</span>@else<span class="badge bg-red-lt">SELISIH</span>@endif</td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Tidak ada stok"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
@endsection

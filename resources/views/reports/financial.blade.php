@extends('layouts.app')
@section('title', 'Laporan Keuangan')
@section('subtitle', $from . ' s.d. ' . $to)
@section('content')
<div class="row row-deck row-cards mb-3">
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Belanja (PO disetujui)</div><div class="h2">{{ mbg_currency($purchases) }}</div></div></div></div>
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Biaya produksi</div><div class="h2">{{ mbg_currency($costings->sum('total_cost')) }}</div></div></div></div>
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Rugi waste</div><div class="h2 text-red">{{ mbg_currency($wasteLoss) }}</div></div></div></div>
</div>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="from" type="date" class="form-control" value="{{ $from }}"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="{{ $to }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Sumber</th><th class="text-end">Material</th><th class="text-end">Total</th><th class="text-end">Per porsi</th></tr></thead>
<tbody>
@foreach($costings as $c)
<tr><td class="text-secondary">{{ $c->costing_date }}</td><td>{{ $c->productionOrder->number ?? '-' }}</td><td class="text-end">{{ mbg_currency($c->material_cost) }}</td><td class="text-end">{{ mbg_currency($c->total_cost) }}</td><td class="text-end">{{ mbg_currency($c->cost_per_portion) }}</td></tr>
@endforeach
</tbody></table></div>
</div></div>
@endsection

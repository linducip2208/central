@extends('layouts.app')
@section('title', 'Costing')
@section('content')
<div class="row row-deck row-cards mb-3">
<div class="col-sm-6"><div class="card"><div class="card-body"><div class="text-secondary">Total biaya (filter saat ini)</div><div class="h1">{{ mbg_currency($summary['total'] ?? 0) }}</div></div></div></div>
<div class="col-sm-6"><div class="card"><div class="card-body"><div class="text-secondary">Rata-rata per porsi</div><div class="h1">{{ mbg_currency($summary['avg_per_portion'] ?? 0) }}</div></div></div></div>
</div>
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>WO / Menu</th><th class="text-end">Material</th><th class="text-end">Tenaga</th><th class="text-end">Overhead</th><th class="text-end">Total</th><th class="text-end">Porsi</th><th class="text-end">Per porsi</th><th></th></tr></thead>
<tbody>
@forelse($costings as $c)
<tr>
<td class="text-secondary">{{ $c->costing_date }}</td>
<td>{{ $c->productionOrder->number ?? $c->menu->name ?? '-' }}</td>
<td class="text-end">{{ mbg_currency($c->material_cost) }}</td>
<td class="text-end">{{ mbg_currency($c->labor_cost) }}</td>
<td class="text-end">{{ mbg_currency($c->overhead_cost) }}</td>
<td class="text-end fw-bold">{{ mbg_currency($c->total_cost) }}</td>
<td class="text-end">{{ number_format($c->portions) }}</td>
<td class="text-end">{{ mbg_currency($c->cost_per_portion) }}</td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('costings.show', $c) }}">Detail</a></td>
</tr>
@empty<tr><td colspan="9"><x-empty title="Belum ada data costing. Costing dibuat otomatis saat WO diselesaikan."/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $costings->links() }}</div>
</div></div>
@endsection

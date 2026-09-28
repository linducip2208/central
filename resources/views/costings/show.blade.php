@extends('layouts.app')
@section('title', 'Costing ' . $costing->costing_date)
@section('content')
<div class="card"><div class="card-body">
<dl class="row">
<dt class="col-4">WO</dt><dd class="col-8">{{ $costing->productionOrder->number ?? '-' }} ({{ $costing->productionOrder->product->name ?? '' }})</dd>
<dt class="col-4">Menu</dt><dd class="col-8">{{ $costing->menu->name ?? '-' }}</dd>
<dt class="col-4">Material (aktual ledger)</dt><dd class="col-8">{{ mbg_currency($costing->material_cost) }}</dd>
<dt class="col-4">Tenaga</dt><dd class="col-8">{{ mbg_currency($costing->labor_cost) }}</dd>
<dt class="col-4">Overhead</dt><dd class="col-8">{{ mbg_currency($costing->overhead_cost) }}</dd>
<dt class="col-4">Kemasan</dt><dd class="col-8">{{ mbg_currency($costing->packaging_cost) }}</dd>
<dt class="col-4">Distribusi</dt><dd class="col-8">{{ mbg_currency($costing->delivery_cost) }}</dd>
<dt class="col-4 fw-bold">Total</dt><dd class="col-8 fw-bold">{{ mbg_currency($costing->total_cost) }}</dd>
<dt class="col-4">Porsi</dt><dd class="col-8">{{ number_format($costing->portions) }}</dd>
<dt class="col-4 fw-bold">Per porsi</dt><dd class="col-8 fw-bold text-green">{{ mbg_currency($costing->cost_per_portion) }}</dd>
</dl>
@if($standard)
<h4 class="mt-3">Standar (teoritis) vs Aktual</h4>
<div class="table-responsive"><table class="table table-vcenter">
<thead><tr><th></th><th class="text-end">Material</th><th class="text-end">Per porsi</th></tr></thead>
<tbody>
<tr><td>Standar (resep × harga std)</td><td class="text-end">{{ mbg_currency($standard['material']) }}</td><td class="text-end">{{ mbg_currency($costing->portions > 0 ? $standard['material'] / $costing->portions : 0) }}</td></tr>
<tr><td>Aktual (ledger)</td><td class="text-end">{{ mbg_currency($costing->material_cost) }}</td><td class="text-end">{{ mbg_currency($costing->cost_per_portion) }}</td></tr>
<tr class="fw-bold"><td>Variansi</td><td class="text-end @if($variance['material'] > 0) text-red @else text-green @endif">{{ $variance['material'] > 0 ? '+' : '' }}{{ mbg_currency($variance['material']) }}</td><td class="text-end @if($variance['per_portion'] > 0) text-red @else text-green @endif">{{ $variance['per_portion'] > 0 ? '+' : '' }}{{ mbg_currency($variance['per_portion']) }}</td></tr>
</tbody></table></div>
<div class="table-responsive mt-2"><table class="table table-sm table-vcenter">
<thead><tr><th>Komponen standar</th><th class="text-end">Qty</th><th class="text-end">Harga std</th><th class="text-end">Biaya</th></tr></thead>
<tbody>
@foreach($standard['lines'] as $l)
<tr><td>{{ $l['ingredient'] }}</td><td class="text-end">{{ number_format($l['qty'], 3) }}</td><td class="text-end">{{ mbg_currency($l['price']) }}</td><td class="text-end">{{ mbg_currency($l['cost']) }}</td></tr>
@endforeach
</tbody></table></div>
@endif
</div></div>
@endsection

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
</div></div>
@endsection

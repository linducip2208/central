@extends('layouts.app')
@section('title', 'Plan ' . $plan->number)
@section('subtitle', $plan->plan_date . ' · target ' . number_format($plan->target_portions) . ' porsi · menu: ' . ($plan->menu->name ?? '-'))
@section('actions')
@if($plan->status === 'DRAFT')
<form method="POST" action="{{ route('production-plans.approve', $plan) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Setujui</button></form>
@endif
@if($plan->status === 'APPROVED')
<form method="POST" action="{{ route('production-plans.generate', $plan) }}" class="d-inline">@csrf<button class="btn btn-primary" type="submit">Generate production orders</button></form>
@endif
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Item rencana <span class="ms-2"><x-badge :status="$plan->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Produk</th><th class="text-end">Rencana</th></tr></thead>
<tbody>
@foreach($plan->items as $it)
<tr><td>{{ $it->product->name ?? '-' }}</td><td class="text-end">{{ number_format($it->planned_qty) }}</td></tr>
@endforeach
</tbody></table></div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Production orders ({{ $plan->orders->count() }})</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Produk</th><th class="text-end">Hasil</th><th>Status</th></tr></thead>
<tbody>
@forelse($plan->orders as $o)
<tr><td><a href="{{ route('production-orders.show', $o) }}">{{ $o->number }}</a></td><td class="text-secondary">{{ $o->product->name ?? '' }}</td><td class="text-end">{{ number_format($o->produced_qty, 0) }}/{{ number_format($o->planned_qty, 0) }}</td><td><x-badge :status="$o->status"/></td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Belum digenerate.</td></tr>@endforelse
</tbody></table></div></div>
</div>
</div>
@endsection

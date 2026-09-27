@extends('layouts.app')
@section('title', $kitchen->name)
@section('subtitle', $kitchen->code . ' · kapasitas ' . number_format($kitchen->daily_capacity) . ' porsi/hari')
@section('content')
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Unit dapur ({{ $kitchen->kitchenUnits->count() }})</h3></div>
<div class="list-group list-group-flush">
@forelse($kitchen->kitchenUnits as $u)
<div class="list-group-item d-flex justify-content-between"><div><div class="fw-bold">{{ $u->name }}</div><div class="text-secondary small">{{ $u->unit_type }} · kap. {{ number_format($u->capacity) }}</div></div><x-badge :status="$u->status"/></div>
@empty<div class="list-group-item text-secondary">Belum ada unit.</div>@endforelse
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Gudang ({{ $kitchen->warehouses->count() }})</h3></div>
<div class="list-group list-group-flush">
@forelse($kitchen->warehouses as $w)
<div class="list-group-item d-flex justify-content-between"><div><div class="fw-bold">{{ $w->name }}</div><div class="text-secondary small">{{ $w->warehouse_type }} @if($w->is_default)· default @endif</div></div><x-badge :status="$w->status"/></div>
@empty<div class="list-group-item text-secondary">Belum ada gudang.</div>@endforelse
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Sekolah dilayani ({{ $kitchen->schools->count() }})</h3></div>
<div class="list-group list-group-flush">
@forelse($kitchen->schools->take(10) as $s)
<div class="list-group-item d-flex justify-content-between"><span>{{ $s->name }}</span><span class="text-secondary">{{ number_format($s->target_portions) }}</span></div>
@empty<div class="list-group-item text-secondary">Belum ada sekolah.</div>@endforelse
</div></div>
</div>
</div>
@endsection

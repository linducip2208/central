@extends('layouts.app')
@section('title', 'Distribusi ' . $dist->number)
@section('subtitle', $dist->distribution_date . ' · ' . ($dist->vehicle_no ?? '-') . ' · ' . ($dist->driver_name ?? '-'))
@section('actions')
@if($dist->status === 'PLANNED')
<form method="POST" action="{{ route('distributions.dispatch', $dist) }}" class="d-inline">@csrf<button class="btn btn-primary" type="submit">Berangkatkan</button></form>
@endif
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Alokasi <span class="ms-2"><x-badge :status="$dist->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Sekolah</th><th>Produk</th><th class="text-end">Rencana</th><th class="text-end">Terkirim</th></tr></thead>
<tbody>
@foreach($dist->items as $it)
<tr><td>{{ $it->school->name ?? '-' }}</td><td class="text-secondary">{{ $it->product->name ?? '-' }}</td><td class="text-end">{{ number_format($it->qty_planned) }}</td><td class="text-end">{{ number_format($it->qty_delivered) }}</td></tr>
@endforeach
</tbody></table></div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Delivery ({{ $dist->deliveries->count() }})</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Sekolah</th><th class="text-end">Terkirim</th><th>Status</th></tr></thead>
<tbody>
@foreach($dist->deliveries as $d)
<tr><td><a href="{{ route('deliveries.show', $d) }}">{{ $d->number }}</a></td><td class="text-secondary">{{ $d->school->name ?? '' }}</td><td class="text-end">{{ number_format($d->qty_delivered) }}/{{ number_format($d->qty_planned) }}</td><td><x-badge :status="$d->status"/></td></tr>
@endforeach
</tbody></table></div></div>
</div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Recall ' . $recall->number)
@section('subtitle', $recall->reason . ' · ' . $recall->severity . ' · pemicu: ' . ($recall->triggerBatch->batch_no ?? '-'))
@section('actions')
@if($recall->status === 'DRAFT')
<form method="POST" action="{{ route('recalls.activate', $recall) }}" class="d-inline" onsubmit="return confirm('Aktifkan recall dan karantina batch?')">@csrf<button class="btn btn-danger" type="submit">Aktifkan + karantina</button></form>
@endif
@if($recall->status === 'ACTIVE')
<form method="POST" action="{{ route('recalls.contain', $recall) }}" class="d-inline">@csrf<div class="input-group"><input name="actions_taken" class="form-control" placeholder="Tindakan yang diambil *" required/><button class="btn btn-warning" type="submit">Terkendali</button></div></form>
@endif
@if($recall->status === 'CONTAINED')
<form method="POST" action="{{ route('recalls.close', $recall) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Tutup recall</button></form>
@endif
@endsection
@section('content')
<div class="card mb-3"><div class="card-body">
<p>{{ $recall->description }}</p>
@if($recall->actions_taken)<div class="alert alert-info mb-0"><strong>Tindakan:</strong> {{ $recall->actions_taken }}</div>@endif
<span class="mt-2 d-inline-block"><x-badge :status="$recall->status"/></span>
</div></div>
<div class="card"><div class="card-header"><h3 class="card-title">Sekolah/penerima terdampak (terkirim)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Delivery</th><th>Sekolah</th><th>Status</th><th>Tanggal</th></tr></thead>
<tbody>
@forelse($forward['deliveries'] as $d)
<tr><td>{{ $d['number'] }}</td><td>{{ $d['school'] }}</td><td><x-badge :status="$d['status']"/></td><td class="text-secondary">{{ $d['date'] }}</td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Belum ada pengiriman dari batch terdampak — fokus karantina stok.</td></tr>@endforelse
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Batch terdampak ({{ $recall->items->count() }})</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Batch</th><th>Gudang</th><th class="text-end">Stok saat recall</th><th class="text-end">Sudah terkirim</th><th>Status kini</th><th>Aksi</th></tr></thead>
<tbody>
@foreach($recall->items as $it)
<tr>
<td><a href="{{ route('trace.batch', $it->batch) }}">{{ $it->batch->batch_no ?? '' }}</a></td>
<td class="text-secondary">{{ $it->batch->warehouse->name ?? '' }}</td>
<td class="text-end">{{ number_format($it->stock_on_hand, 2) }}</td>
<td class="text-end text-red">{{ number_format($it->qty_delivered, 2) }}</td>
<td><x-badge :status="$it->batch->status ?? '?'"/></td>
<td>{{ $it->action }}</td>
</tr>
@endforeach
</tbody></table></div></div>
@endsection

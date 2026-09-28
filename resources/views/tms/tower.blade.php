@extends('layouts.app')
@section('title', 'Delivery Control Tower')
@section('subtitle', 'Posisi hari ini · keterlambatan vs ETA · status POD')
@section('content')
<div class="row row-deck row-cards mb-3">
@foreach([['Total', $stats['total'], 'blue'], ['Berjalan', $stats['dispatched'], 'yellow'], ['Terkirim', $stats['delivered'], 'green'], ['Parsial', $stats['partial'], 'orange'], ['Terlambat', $stats['late'], 'red'], ['Gagal', $stats['failed'], 'red'], ['Tanpa POD', $stats['no_pod'], 'dark']] as [$label, $val, $color])
<div class="col"><div class="card"><div class="card-body p-2 text-center"><div class="text-secondary small">{{ $label }}</div><div class="h2 mb-0 text-{{ $color }}">{{ $val }}</div></div></div></div>
@endforeach
</div>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="date" type="date" class="form-control" value="{{ request('date', today()->toDateString()) }}"/></div>
<div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()"><option value="">— Aktif —</option>@foreach(['PLANNED','IN_TRANSIT','DELIVERED','PARTIAL','FAILED'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th class="text-end">#</th><th>Delivery</th><th>Sekolah</th><th>Rute/Kendaraan</th><th>Kurir</th><th>ETA</th><th>Tiba</th><th>POD</th><th>Status</th></tr></thead>
<tbody>
@forelse($deliveries as $d)
<tr>
<td class="text-end text-secondary">{{ $d->stop_sequence ?: '—' }}</td>
<td><a href="{{ route('deliveries.show', $d) }}">{{ $d->number }}</a></td>
<td>{{ $d->school->name ?? '' }}</td>
<td class="text-secondary">{{ $d->route->name ?? '—' }} · {{ $d->vehicle->plate_no ?? '—' }}</td>
<td class="text-secondary">{{ $d->courier->name ?? '—' }}</td>
<td class="text-secondary">{{ $d->eta?->format('H:i') ?? '—' }}</td>
<td class="@if($d->isLate()) text-red fw-bold @endif">{{ $d->actual_arrival?->format('H:i') ?? '—' }}</td>
<td>@if($d->delivery_proof)<a href="{{ Storage::url($d->delivery_proof) }}" target="_blank" class="badge bg-green-lt">ADA</a>@else<span class="badge bg-dark-lt">—</span>@endif</td>
<td><x-badge :status="$d->status"/></td>
</tr>
@empty<tr><td colspan="9"><x-empty title="Tidak ada delivery pada filter ini"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
@endsection

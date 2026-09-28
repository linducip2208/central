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

<div class="row g-3 mt-1">
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Operasional hari ini</h3></div>
<div class="list-group list-group-flush">
<div class="list-group-item d-flex justify-content-between"><span>Produksi (porsi)</span><strong>{{ number_format($tower['production_today']) }}</strong></div>
<div class="list-group-item d-flex justify-content-between"><span>WO menunggu</span><a href="{{ route('production-orders.index') }}?status=PLANNED">{{ $tower['pending_production'] }}</a></div>
<div class="list-group-item d-flex justify-content-between"><span>Bahan risiko stok</span><a href="{{ route('reports.intelligence') }}">{{ $tower['stock_risk'] }}</a></div>
<div class="list-group-item d-flex justify-content-between"><span>Batch risiko expired</span><a href="{{ route('reports.expiry', ['days' => 14]) }}">{{ $tower['expiry_risk'] }}</a></div>
<div class="list-group-item d-flex justify-content-between"><span>PO menunggu</span><a href="{{ route('purchase-orders.index') }}?status=SUBMITTED">{{ $tower['procurement_pending'] }}</a></div>
<div class="list-group-item d-flex justify-content-between"><span>Rugi waste hari ini</span><span class="text-red">{{ mbg_currency($tower['waste_today']) }}</span></div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title text-red">Perlu perhatian</h3></div>
<div class="list-group list-group-flush">
<div class="list-group-item"><div class="fw-bold">Terlambat ({{ $lateDeliveries->count() }})</div>@foreach($lateDeliveries as $d)<div class="small"><a href="{{ route('deliveries.show', $d) }}">{{ $d->number }}</a> · {{ $d->school->name ?? '' }}</div>@endforeach</div>
<div class="list-group-item"><div class="fw-bold">Gagal ({{ $failedDeliveries->count() }})</div>@foreach($failedDeliveries as $d)<div class="small"><a href="{{ route('deliveries.show', $d) }}">{{ $d->number }}</a> · {{ $d->school->name ?? '' }}</div>@endforeach</div>
<div class="list-group-item"><div class="fw-bold">NCR terbuka ({{ $openNcrs->count() }})</div>@foreach($openNcrs as $n)<div class="small"><a href="{{ route('ncrs.show', $n) }}">{{ $n->number }}</a> · {{ $n->severity }}</div>@endforeach</div>
<div class="list-group-item"><div class="fw-bold">CAPA overdue ({{ $overdueCapas->count() }})</div>@foreach($overdueCapas as $c)<div class="small">{{ $c->action }} ({{ $c->due_date }})</div>@endforeach</div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Pengadaan & keluhan</h3></div>
<div class="list-group list-group-flush">
<div class="list-group-item"><div class="fw-bold">PO menunggu ({{ $pendingPos->count() }})</div>@foreach($pendingPos as $p)<div class="small"><a href="{{ route('purchase-orders.show', $p) }}">{{ $p->number }}</a> · {{ $p->supplier->name ?? '' }}</div>@endforeach</div>
<div class="list-group-item"><div class="fw-bold">Keluhan terbaru ({{ $recentComplaints->count() }})</div>@foreach($recentComplaints as $c)<div class="small">{{ $c->school->name ?? '' }}: {{ \Illuminate\Support\Str::limit($c->complaint, 60) }}</div>@endforeach</div>
</div></div>
</div>
</div>
@endsection

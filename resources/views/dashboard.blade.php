@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan operasional hari ini — ' . now()->translatedFormat('l, d F Y'))

@section('content')
<div class="row row-deck row-cards mb-3">
<div class="col-sm-6 col-lg-3">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-green-lt me-3"><i class="ti ti-bowl"></i></span>
<div><div class="text-secondary">Porsi diproduksi hari ini</div><div class="h1 mb-0">{{ number_format($stats['portions_today']) }}</div></div></div>
</div></div>
</div>
<div class="col-sm-6 col-lg-3">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-blue-lt me-3"><i class="ti ti-shopping-cart"></i></span>
<div><div class="text-secondary">PO aktif</div><div class="h1 mb-0">{{ number_format($stats['active_pos']) }}</div></div></div>
</div></div>
</div>
<div class="col-sm-6 col-lg-3">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-yellow-lt me-3"><i class="ti ti-truck"></i></span>
<div><div class="text-secondary">Pengiriman berjalan</div><div class="h1 mb-0">{{ number_format($stats['in_transit']) }}</div></div></div>
</div></div>
</div>
<div class="col-sm-6 col-lg-3">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-purple-lt me-3"><i class="ti ti-school"></i></span>
<div><div class="text-secondary">Sekolah dilayani</div><div class="h1 mb-0">{{ number_format($stats['schools']) }}</div></div></div>
</div></div>
</div>
</div>

<div class="row row-deck row-cards">
<div class="col-lg-8">
<div class="card">
<div class="card-header"><h3 class="card-title">Produksi 7 hari terakhir (porsi)</h3></div>
<div class="card-body"><canvas id="chartProd" height="120"></canvas></div>
</div>
<div class="card mt-3">
<div class="card-header"><h3 class="card-title">Mutasi stok terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Gudang</th><th>Tipe</th><th>Item</th><th class="text-end">Qty</th></tr></thead>
<tbody>
@forelse($recentMovements as $m)
<tr>
<td class="text-secondary">{{ $m->created_at->format('d M H:i') }}</td>
<td>{{ $m->warehouse->name ?? '-' }}</td>
<td><span class="badge bg-blue-lt">{{ $m->movement_type }}</span></td>
<td>{{ $m->item_type }} #{{ $m->item_id }}</td>
<td class="text-end {{ $m->direction === 'IN' ? 'text-green' : 'text-red' }}">{{ $m->direction === 'IN' ? '+' : '-' }}{{ number_format($m->qty, 2) }}</td>
</tr>
@empty
<tr><td colspan="5" class="text-center text-secondary py-4">Belum ada mutasi.</td></tr>
@endforelse
</tbody>
</table></div>
</div>
</div>
<div class="col-lg-4">
<div class="card">
<div class="card-header"><h3 class="card-title">Stok menipis</h3></div>
<div class="list-group list-group-flush">
@forelse($lowStock as $row)
<div class="list-group-item d-flex justify-content-between align-items-center">
<div><div class="fw-bold">{{ $row['ingredient']->name }}</div><div class="text-secondary small">Min. {{ number_format($row['min'], 2) }} {{ $row['ingredient']->unit->symbol ?? '' }}</div></div>
<span class="badge bg-red-lt">{{ number_format($row['stock'], 2) }}</span>
</div>
@empty
<div class="list-group-item text-secondary">Semua stok aman.</div>
@endforelse
</div>
</div>
<div class="card mt-3">
<div class="card-header"><h3 class="card-title">Mendekati kedaluwarsa</h3></div>
<div class="list-group list-group-flush">
@forelse($expiring as $b)
<div class="list-group-item d-flex justify-content-between align-items-center">
<div><div class="fw-bold">{{ $b->batch_no }}</div><div class="text-secondary small">{{ $b->warehouse->name ?? '' }} · sisa {{ number_format($b->remaining_qty, 2) }}</div></div>
<span class="badge bg-yellow-lt">{{ $b->expiry_date?->format('d M Y') }}</span>
</div>
@empty
<div class="list-group-item text-secondary">Tidak ada batch kritis.</div>
@endforelse
</div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('chartProd');
if (ctx) {
new Chart(ctx, {type: 'bar',
data: {labels: {!! json_encode($production7->map(fn($r) => \Carbon\Carbon::parse($r->production_date)->format('d M'))->values()) !!},
datasets: [{data: {!! json_encode($production7->pluck('qty')->values()) !!}, backgroundColor: '#2fb344', borderRadius: 4}]},
options: {plugins: {legend: {display: false}}, scales: {y: {beginAtZero: true}}}});
}
</script>
@endpush

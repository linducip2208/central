@extends('layouts.app')
@section('title', 'Inventory Intelligence')
@section('content')
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Risiko stockout (≤ minimum)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Tersedia</th><th class="text-end">Min</th><th class="text-end">Hari stok</th></tr></thead>
<tbody>
@forelse($stockout as $s)
<tr><td>{{ $s['name'] }}</td><td class="text-end text-red">{{ number_format($s['available'], 2) }}</td><td class="text-end">{{ number_format($s['min'], 2) }}</td><td class="text-end">{{ $s['days'] ?? '—' }}</td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Tidak ada risiko stockout.</td></tr>@endforelse
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Excess stock (&gt; maksimum)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Tersedia</th><th class="text-end">Max</th></tr></thead>
<tbody>
@forelse($excess as $s)
<tr><td>{{ $s['name'] }}</td><td class="text-end">{{ number_format($s['available'], 2) }}</td><td class="text-end">{{ number_format($s['max'], 2) }}</td></tr>
@empty<tr><td colspan="3" class="text-center text-secondary py-3">Tidak ada excess.</td></tr>@endforelse
</tbody></table></div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Risiko expired ≤30 hari</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Batch</th><th>Expired</th><th class="text-end">Sisa</th></tr></thead>
<tbody>
@forelse($expiryRisk as $b)
<tr><td><a href="{{ route('trace.batch', $b) }}">{{ $b->batch_no }}</a></td><td>{{ $b->expiry_date }}</td><td class="text-end">{{ number_format($b->remaining_qty, 2) }}</td></tr>
@empty<tr><td colspan="3" class="text-center text-secondary py-3">Aman.</td></tr>@endforelse
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Turnover (hari stok, tercepat habis dulu)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Stok</th><th class="text-end">Pakai/hari</th><th class="text-end">Hari</th></tr></thead>
<tbody>
@foreach(array_slice($turnover, 0, 15) as $t)
<tr><td>{{ $t['name'] }}</td><td class="text-end">{{ number_format($t['on_hand'], 2) }}</td><td class="text-end">{{ number_format($t['avg_daily'], 2) }}</td><td class="text-end">{{ $t['days'] ?? '∞' }}</td></tr>
@endforeach
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Dead stock (tanpa movement 60 hari)</h3></div>
<div class="list-group list-group-flush">
@forelse($deadStock as $d)
<div class="list-group-item">{{ $d->name }}</div>
@empty<div class="list-group-item text-secondary">Tidak ada dead stock.</div>@endforelse
</div></div>
</div>
</div>
@endsection

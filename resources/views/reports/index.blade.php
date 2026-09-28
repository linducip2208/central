@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<div class="row row-deck row-cards">
@php
$cards = [
['Stok & nilai persediaan', 'reports.stock', 'ti-box', 'Posisi stok per gudang + valuasi.', []],
['Produksi', 'reports.production', 'ti-chef-hat', 'Hasil produksi per periode.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Pengiriman & fulfillment', 'reports.delivery', 'ti-truck', 'Terkirim vs rencana per sekolah.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Keuangan', 'reports.financial', 'ti-coins', 'Biaya produksi, belanja, rugi waste.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Kedaluarsa', 'reports.expiry', 'ti-alarm', 'Batch mendekati expired.', ['days' => 30]],
['Intelligence', 'reports.intelligence', 'ti-brain', 'Risiko stockout/expired, excess, turnover, dead stock.', []],
['Waste Analytics', 'reports.waste', 'ti-trash', 'Rugi per alasan + tren.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Supplier Scorecard', 'reports.supplier', 'ti-building', 'Ketepatan, belanja, QC gagal.', ['from' => now()->subDays(90)->toDateString(), 'to' => now()->toDateString()]],
['Recall', 'reports.recall', 'ti-alert-triangle', 'Riwayat recall + batch terdampak.', []],
['Nutrisi vs Target', 'reports.nutrition', 'ti-apple', 'Capaian gizi menu vs target.', []],
['AP Aging', 'reports.ap-aging', 'ti-file-invoice', 'Utang supplier belum bayar per jatuh tempo.', []],
['Biaya Sekolah', 'reports.school-cost', 'ti-school', 'Alokasi biaya per sekolah.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Harian Dapur', 'reports.daily', 'ti-calendar', 'Ringkasan operasional 1 hari + cetak.', ['date' => now()->toDateString()]],
['Eksepsi', 'reports.exceptions', 'ti-alert-triangle', 'Semua anomali + drill-down.', []],
];
@endphp
@foreach($cards as [$title, $route, $icon, $desc, $params])
<div class="col-md-4">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center mb-2"><span class="avatar bg-blue-lt me-2"><i class="ti {{ $icon }}"></i></span><h3 class="card-title mb-0">{{ $title }}</h3></div>
<p class="text-secondary">{{ $desc }}</p>
<a href="{{ route($route, $params) }}" class="btn btn-white">Buka laporan</a>
</div></div>
</div>
@endforeach
</div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Laporan terjadwal</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('reports.schedules.store') }}" class="row g-2">@csrf
<div class="col-md-4"><select name="dataset" class="form-select">@foreach(['movements','deliveries','production','waste','costing'] as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach</select></div>
<div class="col-md-4"><select name="frequency" class="form-select"><option value="DAILY">DAILY</option><option value="WEEKLY">WEEKLY</option><option value="MONTHLY">MONTHLY</option></select></div>
<div class="col-md-4"><button class="btn btn-primary" type="submit">Jadwalkan</button></div>
</form>
</div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Dataset</th><th>Frekuensi</th><th>Terakhir jalan</th><th>Aktif</th><th></th></tr></thead>
<tbody>
@forelse($schedules as $s)
<tr><td>{{ $s->dataset }}</td><td>{{ $s->frequency }}</td><td class="text-secondary">{{ $s->last_run_at?->format('d M Y H:i') ?? '—' }}</td><td>@if($s->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">OFF</span>@endif</td>
<td class="text-end"><form method="POST" action="{{ route('reports.schedules.toggle', $s) }}">@csrf<button class="btn btn-sm btn-white" type="submit">Toggle</button></form></td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-2">Belum ada jadwal.</td></tr>@endforelse
</tbody></table></div></div>
@endsection

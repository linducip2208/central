@extends('layouts.app')
@section('title', 'Executive Dashboard')
@section('subtitle', $from . ' s.d. ' . $to)
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-2"><select name="preset" class="form-select" onchange="this.form.submit()">@foreach(['custom' => 'Kustom', 'today' => 'Hari ini', 'yesterday' => 'Kemarin', 'week' => 'Minggu ini', 'month' => 'Bulan ini', 'quarter' => 'Kuartal', 'year' => 'Tahun ini'] as $k => $v)<option value="{{ $k }}" @selected(request('preset', 'custom') === $k)>{{ $v }}</option>@endforeach</select></div>
<div class="col-md-3"><input name="from" type="date" class="form-control" value="{{ $from }}"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="{{ $to }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
<div class="col-md-auto ms-auto">
<div class="dropdown">
<button class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown">Export CSV</button>
<div class="dropdown-menu">
@foreach(['movements','deliveries','production','waste','costing'] as $ds)
<a class="dropdown-item" href="{{ route('analytics.export', $ds) }}">{{ ucfirst($ds) }}</a>
@endforeach
</div>
</div>
</div>
</form>
</div></div>

<div class="row row-deck row-cards mb-3">
@foreach([
['Belanja pengadaan', mbg_currency($kpi['procurement']), 'ti-shopping-cart', 'green', 'procurement_spend'],
['Porsi diproduksi', number_format($kpi['portions']), 'ti-chef-hat', 'green', 'portions'],
['Service level', $kpi['service_level'].'%', 'ti-truck', $kpi['service_level'] >= 95 ? 'green' : 'yellow', 'service_level'],
['Rugi waste', mbg_currency($kpi['waste_loss']), 'ti-trash', 'red', 'waste_loss'],
['Biaya/porsi', mbg_currency($kpi['cost_per_portion']), 'ti-coins', 'blue', 'cost_per_portion'],
['QC fail rate', $kpi['qc_fail_rate'].'%', 'ti-flask', $kpi['qc_fail_rate'] > 5 ? 'red' : 'green', 'qc_fail_rate'],
['Sekolah dilayani', number_format($kpi['schools_served']), 'ti-school', 'purple', null],
] as [$label, $val, $icon, $color, $key])
@php $delta = $key ? ($compared[$key]['delta'] ?? 0) : 0; @endphp
<div class="col-sm-6 col-lg-3"><div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-{{ $color }}-lt me-2"><i class="ti {{ $icon }}"></i></span>
<div><div class="text-secondary">{{ $label }}</div><div class="h2 mb-0">{{ $val }}</div>
@if($key)<div class="small {{ $delta == 0 ? 'text-secondary' : ($delta > 0 ? 'text-green' : 'text-red') }}">{{ $delta > 0 ? '▲' : ($delta < 0 ? '▼' : '●') }} {{ $delta }} vs periode lalu</div>@endif
</div></div>
</div></div></div>
@endforeach
</div>

<div class="row row-deck row-cards">
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Tren produksi harian</h3></div>
<div class="card-body"><canvas id="chartExec" height="120"></canvas></div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Waste per alasan</h3></div>
<div class="list-group list-group-flush">
@forelse($wasteByReason as $w)
<div class="list-group-item d-flex justify-content-between"><span>{{ $w->reason }} <span class="text-secondary">({{ number_format($w->qty, 1) }})</span></span><span class="text-red">{{ mbg_currency($w->loss) }}</span></div>
@empty<div class="list-group-item text-secondary">Tidak ada waste.</div>@endforelse
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Advisory (deterministik)</h3></div>
<div class="card-body"><p class="small">{{ $advisories['waste']['summary'] }}</p></div></div>
</div>
</div>
@endsection
@push('scripts')
<script>
const cx = document.getElementById('chartExec');
if (cx) {
new Chart(cx, {type: 'bar',
data: {labels: {!! json_encode($trend->map(fn($r) => \Carbon\Carbon::parse($r->production_date)->format('d M'))->values()) !!},
datasets: [{label: 'Hasil', data: {!! json_encode($trend->pluck('qty')->values()) !!}, backgroundColor: '#2fb344', borderRadius: 4}, {label: 'Reject', data: {!! json_encode($trend->pluck('rej')->values()) !!}, backgroundColor: '#d63939', borderRadius: 4}]},
options: {plugins: {legend: {display: true}}, scales: {y: {beginAtZero: true}}}});
}
</script>
@endpush

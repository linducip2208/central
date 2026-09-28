@extends('layouts.app')
@section('title', 'Demand Forecast')
@section('subtitle', 'Moving-average deterministik + versi + akurasi MAPE + skenario what-if' . ($avgError !== null ? ' · MAPE rata-rata: ' . round($avgError, 1) . '%' : ''))
@section('content')
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Generate forecast</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('forecasts.generate') }}">@csrf
<div class="mb-2"><label class="form-label">Produk *</label>
<select name="product_id" class="form-select" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Horizon (hari) *</label><input name="horizon_days" type="number" min="1" max="90" value="7" class="form-control" required/></div>
<button class="btn btn-primary" type="submit">Generate</button>
</form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Periode</th><th>Produk</th><th>Ver</th><th class="text-end">Forecast</th><th class="text-end">Aktual</th><th class="text-end">MAPE</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($forecasts as $f)
<tr>
<td class="text-secondary">{{ $f->period_start }}–{{ $f->period_end }}</td>
<td>{{ $f->product->name ?? '' }} @if($f->is_scenario)<span class="badge bg-purple-lt">{{ $f->scenario_name }}</span>@endif</td>
<td>v{{ $f->version }}</td>
<td class="text-end">{{ number_format($f->forecast_qty, 0) }}</td>
<td class="text-end">{{ $f->actual_qty !== null ? number_format($f->actual_qty, 0) : '—' }}</td>
<td class="text-end">{{ $f->error_pct !== null ? $f->error_pct.'%' : '—' }}</td>
<td><x-badge :status="$f->status"/></td>
<td class="text-end d-flex gap-1 justify-content-end">
@if($f->actual_qty === null && !$f->is_scenario)
<form method="POST" action="{{ route('forecasts.actual', $f) }}">@csrf<button class="btn btn-sm btn-white" type="submit">Catat aktual</button></form>
@endif
@if(!$f->is_scenario)
<form method="POST" action="{{ route('forecasts.scenario', $f) }}" class="d-flex gap-1">@csrf<input name="factor" type="number" step="0.1" min="0.1" max="5" value="1.2" class="form-control form-control-sm" style="width:70px" title="faktor"/><input name="name" class="form-control form-control-sm" style="width:110px" placeholder="nama skenario" required/><button class="btn btn-sm btn-white" type="submit">What-if</button></form>
@endif
</td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada forecast"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div>
</div>
@endsection

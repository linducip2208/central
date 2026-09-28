@extends('layouts.app')
@section('title', 'Kesehatan Sistem')
@section('content')
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Checks</h3></div>
<div class="list-group list-group-flush">
@foreach($checks as $name => $c)
<div class="list-group-item d-flex justify-content-between align-items-center">
<div><div class="fw-bold text-capitalize">{{ $name }}</div>
<div class="text-secondary small">
@foreach($c as $k => $v)
@if($k !== 'status'){{ $k }}: {{ is_bool($v) ? ($v ? 'ya' : 'tidak') : (is_scalar($v) ? $v : json_encode($v)) }} · @endif
@endforeach
</div></div>
@if(($c['status'] ?? '') === 'healthy')<span class="badge bg-green-lt">HEALTHY</span>@else<span class="badge bg-red-lt">{{ strtoupper($c['status'] ?? 'UNKNOWN') }}</span>@endif
</div>
@endforeach
</div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Antrian & integrasi</h3></div>
<div class="list-group list-group-flush">
<div class="list-group-item d-flex justify-content-between"><span>Jobs menunggu</span><strong>{{ $jobs }}</strong></div>
<div class="list-group-item d-flex justify-content-between"><span>Webhook belum terkirim</span><strong>{{ $failed }}</strong></div>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Scheduler</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Command</th><th>Jadwal</th></tr></thead>
<tbody>
@foreach($schedules as [$cmd, $when])
<tr><td><code>{{ $cmd }}</code></td><td class="text-secondary">{{ $when }}</td></tr>
@endforeach
</tbody></table></div>
<div class="card-body border-top"><p class="text-secondary small mb-0">Pastikan cron <code>* * * * * php artisan schedule:run</code> aktif di server. Versi app: {{ config('mbg.version') }} · Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</p></div>
</div>
</div>
</div>
@endsection

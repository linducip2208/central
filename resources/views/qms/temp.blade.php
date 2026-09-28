@extends('layouts.app')
@section('title', 'Log Suhu CCP')
@section('subtitle', 'Critical Control Points: batas otomatis per checkpoint, OOR wajib tindakan koreksi')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><label class="form-check mt-2"><input type="checkbox" name="oor" value="1" class="form-check-input" @checked(request('oor')) onchange="this.form.submit()"/><span class="form-check-label">Di luar spec saja</span></label></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Checkpoint</th><th class="text-end">Suhu</th><th class="text-end">Spec</th><th>Status</th><th>Koreksi</th></tr></thead>
<tbody>
@forelse($logs as $l)
<tr>
<td class="text-secondary">{{ $l->logged_at->format('d M H:i') }}</td>
<td>{{ $l->checkpoint }}</td>
<td class="text-end fw-bold">{{ $l->temperature_c }}°C</td>
<td class="text-end text-secondary">{{ $l->spec_min ?? '−∞' }} … {{ $l->spec_max ?? '+∞' }}</td>
<td>@if($l->in_spec)<span class="badge bg-green-lt">OK</span>@else<span class="badge bg-red-lt">OOR</span>@endif</td>
<td class="text-secondary small">{{ $l->corrective_action ?? '—' }}</td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada log suhu"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $logs->links() }}</div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Catat suhu</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('temp.store') }}">@csrf
<div class="mb-2"><label class="form-label">Checkpoint *</label>
<select name="checkpoint" class="form-select">@foreach($checkpoints as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Suhu (°C) *</label><input name="temperature_c" type="number" step="0.1" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Tindakan koreksi (bila OOR)</label><input name="corrective_action" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Simpan</button>
</form>
</div></div>
</div>
</div>
@endsection

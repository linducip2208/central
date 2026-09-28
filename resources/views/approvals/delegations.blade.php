@extends('layouts.app')
@section('title', 'Delegasi Persetujuan')
@section('actions')<a href="{{ route('approvals.matrix') }}" class="btn btn-white">Matriks</a><a href="{{ route('approvals.inbox') }}" class="btn btn-ghost-secondary">Inbox</a>@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Dari</th><th>Ke</th><th>Periode</th><th>Aktif</th><th></th></tr></thead>
<tbody>
@forelse($delegations as $d)
<tr><td>{{ $d->delegator->name ?? '' }}</td><td>{{ $d->delegate->name ?? '' }}</td><td class="text-secondary">{{ $d->start_date }} – {{ $d->end_date }}</td><td>@if($d->is_active)<span class="badge bg-green-lt">ON</span>@else<span class="badge bg-secondary-lt">OFF</span>@endif</td>
<td class="text-end"><form method="POST" action="{{ route('approvals.delegations.toggle', $d) }}">@csrf<button class="btn btn-sm btn-white" type="submit">Toggle</button></form></td></tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada delegasi"/></td></tr>@endforelse
</tbody></table></div>
</div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Delegasi baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('approvals.delegations.store') }}">@csrf
<div class="mb-2"><label class="form-label">Dari (pemberi) *</label>
<select name="delegator_id" class="form-select" required>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roles->pluck('name')->join(',') }})</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Ke (penerima) *</label>
<select name="delegate_id" class="form-select" required>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
<div class="row g-2">
<div class="col-6"><label class="form-label">Mulai *</label><input name="start_date" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-6"><label class="form-label">Selesai *</label><input name="end_date" type="date" class="form-control" value="{{ now()->addDays(7)->toDateString() }}" required/></div>
</div>
<button class="btn btn-primary mt-2" type="submit">Simpan</button>
</form>
</div></div>
</div>
</div>
@endsection

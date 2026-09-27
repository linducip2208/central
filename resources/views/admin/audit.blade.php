@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="action" class="form-select" onchange="this.form.submit()"><option value="">— Semua aksi —</option>@foreach(['CREATE','UPDATE','DELETE'] as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>@endforeach</select></div>
<div class="col-md-4"><input name="q" class="form-control" placeholder="Cari model…" value="{{ request('q') }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Filter</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Model</th><th>IP</th></tr></thead>
<tbody>
@forelse($logs as $l)
<tr>
<td class="text-secondary">{{ $l->created_at->format('d M Y H:i:s') }}</td>
<td>{{ $l->user->name ?? 'sistem' }}</td>
<td><span class="badge bg-blue-lt">{{ $l->action }}</span></td>
<td class="text-secondary small">{{ class_basename($l->model_type) }} #{{ $l->model_id }}</td>
<td class="text-secondary">{{ $l->ip_address ?? '-' }}</td>
</tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada log audit"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $logs->links() }}</div>
</div></div>
@endsection

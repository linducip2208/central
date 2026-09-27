@extends('layouts.app')
@section('title', 'Pengguna')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Aktif</th><th></th></tr></thead>
<tbody>
@forelse($users as $u)
<tr>
<td>{{ $u->name }}</td>
<td class="text-secondary">{{ $u->email }}</td>
<td>{{ $u->roles->pluck('name')->join(', ') }}</td>
<td>@if($u->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-red-lt">NONAKTIF</span>@endif</td>
<td class="text-end">
<form method="POST" action="{{ route('users.role', $u) }}" class="d-inline">@csrf
<select name="role" class="form-select form-select-sm d-inline-block" style="width:auto" onchange="this.form.submit()">@foreach($roles as $r)<option value="{{ $r->name }}" @selected($u->hasRole($r->name))>{{ $r->name }}</option>@endforeach</select></form>
<form method="POST" action="{{ route('users.toggle', $u) }}" class="d-inline">@csrf<button class="btn btn-sm btn-white" type="submit">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
</td>
</tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada pengguna"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $users->links() }}</div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah pengguna</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('users.store') }}">@csrf
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Email *</label><input name="email" type="email" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Password *</label><input name="password" type="password" class="form-control" required minlength="8"/></div>
<div class="mb-2"><label class="form-label">Peran *</label>
<select name="role" class="form-select">@foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach</select></div>
<button class="btn btn-primary" type="submit">Buat akun</button>
</form>
</div></div>
</div>
</div>
@endsection

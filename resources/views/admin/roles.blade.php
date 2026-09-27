@extends('layouts.app')
@section('title', 'Peran & Izin')
@section('content')
<div class="row g-3">
@foreach($roles as $role)
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">{{ $role->name }} <span class="badge bg-blue-lt ms-1">{{ $role->permissions->count() }} izin</span></h3></div>
<div class="card-body">
<form method="POST" action="{{ route('roles.permissions', $role) }}">@csrf
@foreach($permissions as $group => $perms)
<div class="mb-2"><div class="fw-bold small text-secondary text-uppercase">{{ $group }}</div>
<div class="row g-1">
@foreach($perms as $p)
<div class="col-md-6"><label class="form-check"><input type="checkbox" name="permissions[]" value="{{ $p->name }}" class="form-check-input" @checked($role->permissions->contains('name', $p->name))/><span class="form-check-label small">{{ $p->name }}</span></label></div>
@endforeach
</div></div>
@endforeach
<button class="btn btn-primary btn-sm" type="submit">Simpan izin</button>
</form>
</div></div>
</div>
@endforeach
</div>
@endsection

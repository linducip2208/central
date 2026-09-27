@extends('layouts.app')
@section('title', 'Organisasi')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Kota</th><th>Status</th></tr></thead>
<tbody>
@forelse($orgs as $o)
<tr><td class="text-secondary">{{ $o->code }}</td><td>{{ $o->name }}</td><td class="text-secondary">{{ $o->city ?? '-' }}</td><td><x-badge :status="$o->status"/></td></tr>
@empty<tr><td colspan="4"><x-empty title="Belum ada organisasi"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $orgs->links() }}</div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah organisasi</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('organizations.store') }}">@csrf
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Kota</label><input name="city" class="form-control"/></div>
<div class="mb-2"><label class="form-label">Telepon</label><input name="phone" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection

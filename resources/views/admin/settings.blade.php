@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Nilai tersimpan</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Key</th><th>Value</th></tr></thead>
<tbody>
@forelse($all as $k => $v)
<tr><td><code>{{ $k }}</code></td><td class="text-secondary small">{{ is_string($v) ? $v : json_encode($v) }}</td></tr>
@empty<tr><td colspan="2" class="text-center text-secondary py-3">Belum ada pengaturan.</td></tr>
@endforelse
</tbody></table></div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah / ubah</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('settings.store') }}">@csrf
<div class="mb-2"><label class="form-label">Key *</label><input name="key" class="form-control" required placeholder="cth. app.tagline"/></div>
<div class="mb-2"><label class="form-label">Value *</label><textarea name="value" class="form-control" rows="3" required></textarea></div>
<button class="btn btn-primary" type="submit">Simpan</button>
</form>
</div></div>
</div>
</div>
@endsection

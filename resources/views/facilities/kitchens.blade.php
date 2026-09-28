@extends('layouts.app')
@section('title', 'Central Kitchen')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th class="text-end">Kapasitas/hari</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($kitchens as $k)
<tr><td class="text-secondary">{{ $k->code }}</td><td><a href="{{ route('central-kitchens.show', $k) }}">{{ $k->name }}</a><div class="text-secondary small">{{ $k->city ?? '' }}</div></td>
<td class="text-end">{{ number_format($k->daily_capacity) }}</td><td><x-badge :status="$k->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('central-kitchens.show', $k) }}">Detail</a></td></tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada dapur"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $kitchens->links() }}</div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah dapur</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('central-kitchens.store') }}">@csrf
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Kota</label><input name="city" class="form-control"/></div>
<div class="mb-2"><label class="form-label">Kapasitas/hari</label><input name="daily_capacity" type="number" min="0" class="form-control"/></div>
<div class="row g-1 mb-2"><div class="col-6"><label class="form-label">Latitude</label><input name="latitude" type="number" step="0.0000001" class="form-control"/></div><div class="col-6"><label class="form-label">Longitude</label><input name="longitude" type="number" step="0.0000001" class="form-control"/></div></div>
<div class="mb-2"><label class="form-label">Penanggung jawab</label><input name="pic_name" class="form-control"/></div>
<div class="mb-2"><label class="form-label">Telepon PJ</label><input name="pic_phone" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection

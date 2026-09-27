@extends('layouts.app')
@section('title', 'Unit Dapur')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Dapur</th><th>Tipe</th><th class="text-end">Kapasitas</th><th>Status</th></tr></thead>
<tbody>
@forelse($units as $u)
<tr><td class="text-secondary">{{ $u->code }}</td><td>{{ $u->name }}</td><td class="text-secondary">{{ $u->centralKitchen->name ?? '-' }}</td><td>{{ $u->unit_type }}</td><td class="text-end">{{ number_format($u->capacity) }}</td><td><x-badge :status="$u->status"/></td></tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada unit"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $units->links() }}</div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah unit</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('kitchen-units.store') }}">@csrf
<div class="mb-2"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Tipe *</label>
<select name="unit_type" class="form-select">@foreach(['PRODUCTION','PACKAGING','STORAGE'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Kapasitas</label><input name="capacity" type="number" min="0" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection

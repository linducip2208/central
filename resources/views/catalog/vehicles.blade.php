@extends('layouts.app')
@section('title', 'Kendaraan')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Plat</th><th>Nama</th><th>Tipe</th><th class="text-end">Kapasitas</th><th>Cooler</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($vehicles as $v)
<tr><td class="fw-bold">{{ $v->plate_no }}</td><td>{{ $v->name }}</td><td>{{ $v->vehicle_type }}</td><td class="text-end">{{ number_format($v->capacity_portions) }}</td><td>@if($v->has_cooler)<span class="badge bg-blue-lt">YA</span>@else — @endif</td><td><x-badge :status="$v->status"/></td>
<td class="text-end"><form method="POST" action="{{ route('catalog.vehicles.destroy', $v) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form></td></tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada kendaraan"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Kendaraan baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('catalog.vehicles.store') }}">@csrf
<div class="mb-2"><label class="form-label">Dapur</label>
<select name="central_kitchen_id" class="form-select"><option value="">—</option>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Plat *</label><input name="plate_no" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Tipe *</label>
<select name="vehicle_type" class="form-select">@foreach(['BOX','PICKUP','MOTOR','VAN'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Kapasitas (porsi)</label><input name="capacity_portions" type="number" min="0" class="form-control"/></div>
<div class="mb-2"><label class="form-check"><input type="checkbox" name="has_cooler" value="1" class="form-check-input"/><span class="form-check-label">Ada pendingin</span></label></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection

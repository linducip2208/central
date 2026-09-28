@extends('layouts.app')
@section('title', 'Work Center')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Dapur</th><th>Tipe</th><th class="text-end">Kap/jam</th><th class="text-end">Operator</th><th></th></tr></thead>
<tbody>
@forelse($centers as $c)
<tr><td class="fw-bold">{{ $c->code }}</td><td>{{ $c->name }}</td><td class="text-secondary">{{ $c->centralKitchen->name ?? '' }}</td><td>{{ $c->center_type }}</td><td class="text-end">{{ number_format($c->capacity_per_hour) }}</td><td class="text-end">{{ $c->operators_required }}</td>
<td class="text-end"><form method="POST" action="{{ route('catalog.work-centers.destroy', $c) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form></td></tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada work center"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Work center baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('catalog.work-centers.store') }}">@csrf
<div class="mb-2"><label class="form-label">Dapur *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Kode *</label><input name="code" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Tipe *</label><input name="center_type" class="form-control" value="COOKING" required/></div>
<div class="mb-2"><label class="form-label">Kapasitas/jam (porsi)</label><input name="capacity_per_hour" type="number" min="0" class="form-control"/></div>
<div class="mb-2"><label class="form-label">Butuh operator</label><input name="operators_required" type="number" min="0" value="1" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection

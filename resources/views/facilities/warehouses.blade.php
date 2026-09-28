@extends('layouts.app')
@section('title', 'Gudang')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Dapur</th><th>Tipe</th><th>Alokasi</th><th>PJ</th><th>Status</th></tr></thead>
<tbody>
@forelse($warehouses as $w)
<tr><td class="text-secondary">{{ $w->code }}</td><td>{{ $w->name }} @if($w->is_default)<span class="badge bg-green-lt">DEFAULT</span>@endif</td><td class="text-secondary">{{ $w->centralKitchen->name ?? '-' }}</td><td>{{ $w->warehouse_type }}</td><td><span class="badge bg-blue-lt">{{ $w->fifo_method ?? 'FEFO' }}</span></td><td class="text-secondary">{{ $w->pic_name ?? '-' }}</td><td><x-badge :status="$w->status"/></td></tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada gudang"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $warehouses->links() }}</div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah gudang</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('warehouses.store') }}">@csrf
<div class="mb-2"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Tipe *</label>
<select name="warehouse_type" class="form-select">@foreach(['DRY','CHILLED','FROZEN','PACKAGING'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Metode alokasi *</label>
<select name="fifo_method" class="form-select"><option value="FEFO">FEFO (expired dulu)</option><option value="FIFO">FIFO (masuk dulu)</option></select></div>
<div class="mb-2"><label class="form-label">Lokasi</label><input name="location" class="form-control"/></div>
<div class="mb-2"><label class="form-label">Penanggung jawab</label><input name="pic_name" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection

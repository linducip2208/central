@extends('layouts.app')
@section('title', 'Buat Distribusi')
@section('subtitle', 'Delivery per sekolah otomatis dibuat dari alokasi di bawah')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('distributions.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Dari packaging</label>
<select name="packaging_id" class="form-select"><option value="">—</option>@foreach($packagings as $p)<option value="{{ $p->id }}">{{ $p->number }} ({{ number_format($p->packages_done) }})</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Kendaraan</label><input name="vehicle_no" class="form-control" placeholder="B 1234 XX"/></div>
<div class="col-md-2"><label class="form-label">Sopir</label><input name="driver_name" class="form-control"/></div>
</div>
<h4 class="mt-4">Alokasi sekolah *</h4>
<div id="dist-items" class="row g-2">
<div class="col-md-5"><select name="items[0][school_id]" class="form-select" required><option value="">— sekolah —</option>@foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ number_format($s->target_portions) }})</option>@endforeach</select></div>
<div class="col-md-4"><select name="items[0][product_id]" class="form-select" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="col-md-3"><input name="items[0][qty]" type="number" min="1" class="form-control" placeholder="porsi" required/></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addDist()">+ Tambah sekolah</button></div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan + buat delivery</button></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let di = 1;
function addDist() {
document.getElementById('dist-items').insertAdjacentHTML('beforeend', `<div class="col-md-5"><select name="items[${di}][school_id]" class="form-select">@foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div><div class="col-md-4"><select name="items[${di}][product_id]" class="form-select">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div><div class="col-md-3"><input name="items[${di}][qty]" type="number" min="1" class="form-control" placeholder="porsi"/></div>`);
di++;
}
</script>
@endpush

@extends('layouts.app')
@section('title', 'Buat BOM')
@section('subtitle', 'Multi-level: komponen bisa bahan ATAU produk (sub-assembly). Siklus otomatis ditolak.')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('boms.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Produk induk *</label>
<select name="product_id" class="form-select" required><option value="">—</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Versi</label><input name="version" class="form-control" value="1.0"/></div>
<div class="col-md-2"><label class="form-label">Berlaku dari</label><input name="effective_from" type="date" class="form-control" value="{{ now()->toDateString() }}"/></div>
<div class="col-md-2"><label class="form-label">Yield *</label><input name="yield_qty" type="number" step="0.001" min="0.001" value="1" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<h4 class="mt-4">Komponen *</h4>
<div id="bom-items" class="row g-2">
<div class="col-md-3"><select name="items[0][component_type]" class="form-select"><option value="ingredient">Bahan</option><option value="product">Sub-assembly (produk)</option><option value="material">Material kemasan</option></select></div>
<div class="col-md-4"><select name="items[0][component_id]" class="form-select" required><option value="">— komponen —</option><optgroup label="Bahan">@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->code }} — {{ $i->name }}</option>@endforeach</optgroup><optgroup label="Produk">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->code }} — {{ $p->name }}</option>@endforeach</optgroup></select></div>
<div class="col-md-2"><input name="items[0][qty]" type="number" step="0.0001" min="0.0001" class="form-control" placeholder="qty" required/></div>
<div class="col-md-2"><select name="items[0][unit_id]" class="form-select">@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->symbol }}</option>@endforeach</select></div>
<div class="col-md-1"><input name="items[0][waste_pct]" type="number" step="0.01" min="0" max="100" value="0" class="form-control" title="waste %"/></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addBom()">+ Tambah komponen</button></div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan BOM</button></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let bi = 1;
function addBom() {
document.getElementById('bom-items').insertAdjacentHTML('beforeend', `<div class="col-md-3"><select name="items[${bi}][component_type]" class="form-select"><option value="ingredient">Bahan</option><option value="product">Sub-assembly</option><option value="material">Material</option></select></div><div class="col-md-4"><select name="items[${bi}][component_id]" class="form-select">@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->code }} — {{ $i->name }}</option>@endforeach</select></div><div class="col-md-2"><input name="items[${bi}][qty]" type="number" step="0.0001" min="0.0001" class="form-control" placeholder="qty"/></div><div class="col-md-2"><select name="items[${bi}][unit_id]" class="form-select">@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->symbol }}</option>@endforeach</select></div><div class="col-md-1"><input name="items[${bi}][waste_pct]" type="number" value="0" class="form-control"/></div>`);
bi++;
}
</script>
@endpush

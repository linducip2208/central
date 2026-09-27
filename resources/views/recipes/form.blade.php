@extends('layouts.app')
@section('title', 'Buat Resep')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('recipes.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Produk *</label>
<select name="product_id" class="form-select" required><option value="">—</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Nama resep *</label><input name="name" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Yield (hasil) *</label><input name="yield_qty" type="number" step="0.001" min="0.01" value="1" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Waktu masak (mnt)</label><input name="cook_time_minutes" type="number" min="0" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Instruksi</label><textarea name="instructions" class="form-control" rows="2"></textarea></div>
</div>
<h4 class="mt-4">Bahan *</h4>
<div id="recipe-items" class="row g-2">
<div class="col-md-5"><select name="items[0][ingredient_id]" class="form-select" required><option value="">— bahan —</option>@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->unit->symbol ?? '' }})</option>@endforeach</select></div>
<div class="col-md-2"><input name="items[0][qty]" type="number" step="0.0001" min="0.0001" class="form-control" placeholder="qty" required/></div>
<div class="col-md-3"><select name="items[0][unit_id]" class="form-select">@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->symbol }}</option>@endforeach</select></div>
<div class="col-md-2"><div class="input-group"><input name="items[0][waste_factor_pct]" type="number" step="0.01" min="0" max="100" value="0" class="form-control"/><span class="input-group-text">% susut</span></div></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addIng()">+ Tambah bahan</button></div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan resep</button><a href="{{ route('recipes.index') }}" class="btn btn-white">Batal</a></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let ri = 1;
function addIng() {
const wrap = document.getElementById('recipe-items');
wrap.insertAdjacentHTML('beforeend', `<div class="col-md-5"><select name="items[${ri}][ingredient_id]" class="form-select">@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach</select></div><div class="col-md-2"><input name="items[${ri}][qty]" type="number" step="0.0001" min="0.0001" class="form-control" placeholder="qty"/></div><div class="col-md-3"><select name="items[${ri}][unit_id]" class="form-select">@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->symbol }}</option>@endforeach</select></div><div class="col-md-2"><input name="items[${ri}][waste_factor_pct]" type="number" value="0" class="form-control"/></div>`);
ri++;
}
</script>
@endpush

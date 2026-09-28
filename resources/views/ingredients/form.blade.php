@extends('layouts.app')
@section('title', ($ingredient->exists ? 'Ubah' : 'Tambah') . ' Bahan Baku')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ $ingredient->exists ? route('ingredients.update', $ingredient) : route('ingredients.store') }}">
@csrf @if($ingredient->exists) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama bahan *</label><input name="name" class="form-control" value="{{ old('name', $ingredient->name) }}" required/></div>
<div class="col-md-3"><label class="form-label">Kategori *</label>
<select name="category" class="form-select">@foreach(['STAPLE','PROTEIN','VEGETABLE','FRUIT','SPICE','OIL','OTHER'] as $c)<option value="{{ $c }}" @selected(old('category', $ingredient->category) === $c)>{{ $c }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Satuan dasar *</label>
<select name="unit_id" class="form-select">@foreach($units as $u)<option value="{{ $u->id }}" @selected(old('unit_id', $ingredient->unit_id) == $u->id)>{{ $u->name }} ({{ $u->symbol }})</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Harga standar *</label><input name="standard_price" type="number" min="0" step="0.01" class="form-control" value="{{ old('standard_price', $ingredient->standard_price ?? 0) }}" required/></div>
<div class="col-md-3"><label class="form-label">Stok minimum</label><input name="min_stock" type="number" min="0" step="0.001" class="form-control" value="{{ old('min_stock', $ingredient->min_stock ?? 0) }}"/></div>
<div class="col-md-3"><label class="form-label">Stok maksimum</label><input name="max_stock" type="number" min="0" step="0.001" class="form-control" value="{{ old('max_stock', $ingredient->max_stock ?? 0) }}"/></div>
<div class="col-md-3"><label class="form-label">Daya simpan (hari)</label><input name="shelf_life_days" type="number" min="0" class="form-control" value="{{ old('shelf_life_days', $ingredient->shelf_life_days ?? 0) }}"/></div>
<div class="col-md-3"><label class="form-label">Reorder point</label><input name="reorder_point" type="number" min="0" step="0.001" class="form-control" value="{{ old('reorder_point', $ingredient->reorder_point ?? 0) }}"/></div>
<div class="col-md-3"><label class="form-label">Safety stock</label><input name="safety_stock" type="number" min="0" step="0.001" class="form-control" value="{{ old('safety_stock', $ingredient->safety_stock ?? 0) }}"/></div>
<div class="col-md-3"><label class="form-label">Lead time (hari)</label><input name="lead_time_days" type="number" min="0" class="form-control" value="{{ old('lead_time_days', $ingredient->lead_time_days ?? 1) }}"/></div>
<div class="col-md-3"><label class="form-label">MOQ</label><input name="moq" type="number" min="0" step="0.001" class="form-control" value="{{ old('moq', $ingredient->moq ?? 0) }}"/></div>
<div class="col-md-5"><label class="form-label">Supplier preferensi</label>
<select name="preferred_supplier_id" class="form-select"><option value="">— otomatis termurah —</option>@foreach(\App\Models\Supplier::active()->get() as $s)<option value="{{ $s->id }}" @selected(old('preferred_supplier_id', $ingredient->preferred_supplier_id) == $s->id)>{{ $s->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Alergen</label>
<div>@foreach(\App\Models\Allergen::all() as $a)<label class="form-check form-check-inline"><input type="checkbox" name="allergens[]" value="{{ $a->id }}" class="form-check-input" @checked(in_array($a->id, old('allergens', $ingredient->allergens->pluck('id')->toArray() ?? [])))/><span class="form-check-label">{{ $a->name }}</span></label>@endforeach</div></div>
<div class="col-md-12"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $ingredient->is_active ?? true))/><span class="form-check-label">Aktif</span></label></div>
</div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan</button><a href="{{ route('ingredients.index') }}" class="btn btn-white">Batal</a></div>
</form>
</div></div>
@endsection

@extends('layouts.app')
@section('title', ($product->exists ? 'Ubah' : 'Tambah') . ' Produk')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
@csrf @if($product->exists) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama produk *</label><input name="name" class="form-control" value="{{ old('name', $product->name) }}" required/></div>
<div class="col-md-3"><label class="form-label">Kategori *</label>
<select name="category" class="form-select">@foreach(['MEAL','SNACK','DRINK','EXTRA'] as $c)<option value="{{ $c }}" @selected(old('category', $product->category) === $c)>{{ $c }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Satuan *</label>
<select name="unit_id" class="form-select">@foreach($units as $u)<option value="{{ $u->id }}" @selected(old('unit_id', $product->unit_id) == $u->id)>{{ $u->name }} ({{ $u->symbol }})</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Ukuran porsi (gram)</label><input name="portion_size_gram" type="number" min="0" class="form-control" value="{{ old('portion_size_gram', $product->portion_size_gram ?? 0) }}"/></div>
<div class="col-md-9"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="2">{{ old('description', $product->description) }}</textarea></div>
<div class="col-md-12"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $product->is_active ?? true))/><span class="form-check-label">Aktif</span></label></div>
</div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan</button><a href="{{ route('products.index') }}" class="btn btn-white">Batal</a></div>
</form>
</div></div>
@endsection

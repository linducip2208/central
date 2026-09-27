@extends('layouts.app')
@section('title', ($supplier->exists ? 'Ubah' : 'Tambah') . ' Supplier')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
@csrf @if($supplier->exists) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama supplier *</label><input name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required/></div>
<div class="col-md-3"><label class="form-label">Kategori *</label>
<select name="category" class="form-select">@foreach(['FOOD','NON_FOOD','SERVICE'] as $c)<option value="{{ $c }}" @selected(old('category', $supplier->category) === $c)>{{ $c }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Status *</label>
<select name="status" class="form-select">@foreach(['ACTIVE','INACTIVE'] as $s)<option value="{{ $s }}" @selected(old('status', $supplier->status ?? 'ACTIVE') === $s)>{{ $s }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Kontak person</label><input name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}"/></div>
<div class="col-md-4"><label class="form-label">Telepon</label><input name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}"/></div>
<div class="col-md-4"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="{{ old('email', $supplier->email) }}"/></div>
<div class="col-md-8"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea></div>
<div class="col-md-4"><label class="form-label">NPWP</label><input name="tax_number" class="form-control" value="{{ old('tax_number', $supplier->tax_number) }}"/></div>
@if($supplier->exists)
<div class="col-md-2"><label class="form-label">Rating (0–5)</label><input name="rating" type="number" min="0" max="5" class="form-control" value="{{ old('rating', $supplier->rating) }}"/></div>
@endif
</div>
<div class="form-footer mt-3 d-flex gap-2">
<button class="btn btn-primary" type="submit">Simpan</button>
<a href="{{ route('suppliers.index') }}" class="btn btn-white">Batal</a>
</div>
</form>
</div></div>
@endsection

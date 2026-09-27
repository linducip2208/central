@extends('layouts.app')
@section('title', 'Buat Stock Opname')
@section('subtitle', 'Snapshot stok sistem per batch otomatis dibuat saat opname disimpan')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('stock-opnames.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Tanggal *</label><input name="opname_date" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-md-4"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Buat + snapshot</button></div>
</form>
</div></div>
@endsection

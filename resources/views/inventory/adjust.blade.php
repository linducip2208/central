@extends('layouts.app')
@section('title', 'Penyesuaian Stok')
@section('subtitle', 'Selisih dicatat sebagai movement ADJUSTMENT — wajib diisi alasan')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('inventory.adjust') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Bahan *</label>
<select name="ingredient_id" class="form-select" required>@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Qty baru (sistem saat ini → diganti) *</label><input name="new_qty" type="number" step="0.001" min="0" class="form-control" required/></div>
<div class="col-md-12"><label class="form-label">Alasan (min. 5 karakter) *</label><textarea name="notes" class="form-control" rows="2" required></textarea></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-warning" type="submit" onclick="return confirm('Posting penyesuaian ke ledger?')">Posting adjustment</button></div>
</form>
</div></div>
@endsection

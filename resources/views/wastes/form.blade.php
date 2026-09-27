@extends('layouts.app')
@section('title', 'Catat Waste')
@section('subtitle', 'Stok berkurang via ledger (FEFO) + nilai kerugian dihitung dari harga batch')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('wastes.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Bahan *</label>
<select name="ingredient_id" class="form-select" required>@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->unit->symbol ?? '' }})</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Qty *</label><input name="qty" type="number" step="0.001" min="0.001" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Alasan *</label>
<select name="reason" class="form-select">@foreach(['EXPIRED','SPOILED','OVER_PRODUCTION','QC_REJECT','OTHER'] as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Metode pembuangan</label><input name="disposal_method" class="form-control" placeholder="cth. kompos, bank sampah"/></div>
<div class="col-md-6"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-danger" type="submit" onclick="return confirm('Catat waste dan kurangi stok?')">Catat waste</button></div>
</form>
</div></div>
@endsection

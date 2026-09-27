@extends('layouts.app')
@section('title', 'Transfer Antar Gudang')
@section('subtitle', 'Stok keluar dari gudang asal memakai FEFO, masuk ke gudang tujuan sebagai batch baru (expiry terbawa)')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('inventory.transfer') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Gudang asal *</label>
<select name="from_warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Gudang tujuan *</label>
<select name="to_warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Bahan *</label>
<select name="ingredient_id" class="form-select" required>@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->unit->symbol ?? '' }})</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Qty *</label><input name="qty" type="number" step="0.001" min="0.001" class="form-control" required/></div>
<div class="col-md-8"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit" onclick="return confirm('Proses transfer?')">Proses transfer</button></div>
</form>
</div></div>
@endsection

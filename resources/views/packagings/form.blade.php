@extends('layouts.app')
@section('title', 'Buat Packaging')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('packagings.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Dari production order</label>
<select name="production_order_id" class="form-select"><option value="">— manual —</option>@foreach($orders as $o)<option value="{{ $o->id }}">{{ $o->number }} — {{ $o->product->name }} ({{ number_format($o->produced_qty, 0) }})</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Gudang</label>
<select name="warehouse_id" class="form-select"><option value="">—</option>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Rencana kemas *</label><input name="packages_planned" type="number" min="1" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Jenis kemas *</label>
<select name="package_type" class="form-select">@foreach(['BOX','TRAY','POUCH','BOTTLE'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
<div class="col-md-12"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan</button></div>
</form>
</div></div>
@endsection

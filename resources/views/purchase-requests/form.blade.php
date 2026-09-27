@extends('layouts.app')
@section('title', 'Buat Purchase Request')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('purchase-requests.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Gudang</label>
<select name="warehouse_id" class="form-select"><option value="">—</option>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Dibutuhkan tanggal</label><input name="needed_date" type="date" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<h4 class="mt-4">Item *</h4>
<div id="pr-items" class="row g-2">
<div class="col-md-8"><select name="items[0][ingredient_id]" class="form-select" required><option value="">— bahan —</option>@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->unit->symbol ?? '' }})</option>@endforeach</select></div>
<div class="col-md-4"><input name="items[0][qty]" type="number" step="0.001" min="0.001" class="form-control" placeholder="qty" required/></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addPr()">+ Tambah item</button></div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan draft</button><a href="{{ route('purchase-requests.index') }}" class="btn btn-white">Batal</a></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let pi = 1;
function addPr() {
document.getElementById('pr-items').insertAdjacentHTML('beforeend', `<div class="col-md-8"><select name="items[${pi}][ingredient_id]" class="form-select">@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach</select></div><div class="col-md-4"><input name="items[${pi}][qty]" type="number" step="0.001" min="0.001" class="form-control" placeholder="qty"/></div>`);
pi++;
}
</script>
@endpush

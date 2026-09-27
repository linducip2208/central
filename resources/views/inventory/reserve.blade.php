@extends('layouts.app')
@section('title', 'Reservasi Stok')
@section('subtitle', 'Stok yang direservasi tidak bisa dikonsumsi sampai dilepas — untuk alokasi delivery/produksi')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('inventory.reserve') }}">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Tipe *</label>
<select name="item_type" id="item-type" class="form-select" onchange="syncItems()"><option value="ingredient">Bahan baku</option><option value="product">Produk jadi</option></select></div>
<div class="col-md-3"><label class="form-label">Item *</label>
<select name="item_id" id="item-id" class="form-select" required></select></div>
<div class="col-md-3"><label class="form-label">Qty *</label><input name="qty" type="number" step="0.001" min="0.001" class="form-control" required/></div>
</div>
<div class="form-footer mt-3 d-flex gap-2">
<button class="btn btn-primary" type="submit">Reservasi</button>
<button class="btn btn-white" type="submit" formaction="{{ route('inventory.release') }}" onclick="return confirm('Lepas reservasi sejumlah ini?')">Lepas reservasi</button>
</div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
const ING = @json($ingredients->map(fn($i) => ['id' => $i->id, 'name' => $i->name]));
const PRD = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
function syncItems() {
const t = document.getElementById('item-type').value;
const sel = document.getElementById('item-id');
const list = t === 'product' ? PRD : ING;
sel.innerHTML = list.map(o => `<option value="${o.id}">${o.name}</option>`).join('');
}
syncItems();
</script>
@endpush

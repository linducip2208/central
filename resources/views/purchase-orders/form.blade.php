@extends('layouts.app')
@section('title', 'Buat Purchase Order')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('purchase-orders.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Supplier *</label>
<select name="supplier_id" class="form-select" required><option value="">—</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Dari PR (opsional)</label>
<select name="purchase_request_id" class="form-select"><option value="">— manual —</option>@foreach($prs as $pr)<option value="{{ $pr->id }}">{{ $pr->number }} ({{ $pr->items->count() }} item)</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Ekspektasi tiba</label><input name="expected_date" type="date" class="form-control"/></div>
<div class="col-md-2"><label class="form-label">Pembayaran *</label>
<select name="payment_terms" class="form-select">@foreach(['CASH','CREDIT','COD'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
</div>
<h4 class="mt-4">Item * <span class="text-secondary small">qty dalam satuan dasar bahan</span></h4>
<div class="table-responsive"><table class="table" id="po-table">
<thead><tr><th>Bahan (kode)</th><th style="width:140px">Qty</th><th style="width:170px">Harga satuan</th><th style="width:60px"></th></tr></thead>
<tbody>
<tr>
<td><select name="items[0][ingredient_id]" class="form-select" required><option value="">—</option>@foreach(\App\Models\Ingredient::active()->get() as $i)<option value="{{ $i->id }}">{{ $i->code }} — {{ $i->name }}</option>@endforeach</select></td>
<td><input name="items[0][qty]" type="number" step="0.001" min="0.001" class="form-control" required/></td>
<td><input name="items[0][price]" type="number" step="0.01" min="0" class="form-control" required/></td>
<td></td>
</tr>
</tbody>
</table></div>
<button type="button" class="btn btn-white btn-sm" onclick="addPo()">+ Tambah item</button>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan draft PO</button><a href="{{ route('purchase-orders.index') }}" class="btn btn-white">Batal</a></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let poi = 1;
function addPo() {
document.querySelector('#po-table tbody').insertAdjacentHTML('beforeend', `<tr><td><select name="items[${poi}][ingredient_id]" class="form-select">@foreach(\App\Models\Ingredient::active()->get() as $i)<option value="{{ $i->id }}">{{ $i->code }} — {{ $i->name }}</option>@endforeach</select></td><td><input name="items[${poi}][qty]" type="number" step="0.001" min="0.001" class="form-control"/></td><td><input name="items[${poi}][price]" type="number" step="0.01" min="0" class="form-control"/></td><td><button type="button" class="btn btn-sm btn-ghost-danger" onclick="this.closest('tr').remove()">×</button></td></tr>`);
poi++;
}
</script>
@endpush

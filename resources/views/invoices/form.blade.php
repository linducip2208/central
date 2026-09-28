@extends('layouts.app')
@section('title', 'Catat Supplier Invoice')
@section('subtitle', 'Duplikat no. invoice per supplier ditolak · 3-way match otomatis vs PO+GR')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('invoices.store') }}">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">Supplier *</label>
<select name="supplier_id" class="form-select" required><option value="">—</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">PO (untuk matching)</label>
<select name="purchase_order_id" class="form-select"><option value="">— tanpa PO —</option>@foreach($pos as $po)<option value="{{ $po->id }}">{{ $po->number }} — {{ $po->supplier->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">No. invoice supplier *</label><input name="supplier_invoice_no" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Tgl invoice *</label><input name="invoice_date" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-md-2"><label class="form-label">Pajak</label><input name="tax_amount" type="number" min="0" value="0" class="form-control"/></div>
</div>
<h4 class="mt-4">Item tagihan *</h4>
<div id="inv-items" class="row g-2">
<div class="col-md-5"><select name="items[0][ingredient_id]" class="form-select" required><option value="">— bahan —</option>@foreach(\App\Models\Ingredient::active()->get() as $i)<option value="{{ $i->id }}">{{ $i->code }} — {{ $i->name }}</option>@endforeach</select></div>
<div class="col-md-3"><input name="items[0][qty]" type="number" step="0.001" min="0.001" class="form-control" placeholder="qty" required/></div>
<div class="col-md-4"><input name="items[0][price]" type="number" step="0.01" min="0" class="form-control" placeholder="harga satuan" required/></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addInv()">+ Tambah item</button></div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan + matching</button></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let ii = 1;
function addInv() {
document.getElementById('inv-items').insertAdjacentHTML('beforeend', `<div class="col-md-5"><select name="items[${ii}][ingredient_id]" class="form-select">@foreach(\App\Models\Ingredient::active()->get() as $i)<option value="{{ $i->id }}">{{ $i->code }} — {{ $i->name }}</option>@endforeach</select></div><div class="col-md-3"><input name="items[${ii}][qty]" type="number" step="0.001" min="0.001" class="form-control" placeholder="qty"/></div><div class="col-md-4"><input name="items[${ii}][price]" type="number" step="0.01" min="0" class="form-control" placeholder="harga"/></div>`);
ii++;
}
</script>
@endpush

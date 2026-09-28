@extends('layouts.app')
@section('title', 'Terima Retur ' . $delivery->number)
@section('subtitle', ($delivery->school->name ?? '') . ' · kirim ' . number_format($delivery->qty_delivered) . ' · sudah retur ' . number_format($delivery->qty_returned))
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('returns.store', $delivery) }}">@csrf
<div class="mb-3 col-md-4"><label class="form-label">Gudang penerima retur *</label>
<select name="warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<h4>Item retur *</h4>
<div id="ret-items" class="row g-2">
<div class="col-md-4"><select name="items[0][product_id]" class="form-select" required>@foreach($delivery->items as $it)<option value="{{ $it->product_id }}">{{ $it->product->name ?? '' }}</option>@endforeach</select></div>
<div class="col-md-2"><input name="items[0][qty]" type="number" min="1" class="form-control" placeholder="qty" required/></div>
<div class="col-md-3"><select name="items[0][reason]" class="form-select"><option>DAMAGED</option><option>WRONG_MENU</option><option>EXCESS</option><option>REFUSED</option><option>OTHER</option></select></div>
<div class="col-md-3"><select name="items[0][condition]" class="form-select"><option value="GOOD">GOOD (bisa restock)</option><option value="DAMAGED">DAMAGED</option><option value="EXPIRED">EXPIRED</option></select></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addRet()">+ Baris</button></div>
<div class="mb-3 mt-2"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
<div class="form-footer"><button class="btn btn-primary" type="submit">Terima retur</button></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let rti = 1;
function addRet() {
document.getElementById('ret-items').insertAdjacentHTML('beforeend', `<div class="col-md-4"><select name="items[${rti}][product_id]" class="form-select">@foreach($delivery->items as $it)<option value="{{ $it->product_id }}">{{ $it->product->name ?? '' }}</option>@endforeach</select></div><div class="col-md-2"><input name="items[${rti}][qty]" type="number" min="1" class="form-control" placeholder="qty"/></div><div class="col-md-3"><select name="items[${rti}][reason]" class="form-select"><option>DAMAGED</option><option>WRONG_MENU</option><option>EXCESS</option><option>REFUSED</option><option>OTHER</option></select></div><div class="col-md-3"><select name="items[${rti}][condition]" class="form-select"><option value="GOOD">GOOD</option><option value="DAMAGED">DAMAGED</option><option value="EXPIRED">EXPIRED</option></select></div>`);
rti++;
}
</script>
@endpush

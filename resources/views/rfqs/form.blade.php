@extends('layouts.app')
@section('title', 'Buat RFQ')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('rfqs.store') }}">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">Dapur *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Dari PR</label>
<select name="purchase_request_id" class="form-select"><option value="">— manual —</option>@foreach($prs as $pr)<option value="{{ $pr->id }}">{{ $pr->number }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Deadline</label><input name="deadline" type="date" class="form-control"/></div>
<div class="col-md-3"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Undang supplier *</label>
<div class="row g-1">@foreach($suppliers as $s)<div class="col-md-3"><label class="form-check"><input type="checkbox" name="supplier_ids[]" value="{{ $s->id }}" class="form-check-input" checked/><span class="form-check-label">{{ $s->name }}</span></label></div>@endforeach</div></div>
</div>
<h4 class="mt-4">Item *</h4>
<div id="rfq-items" class="row g-2">
<div class="col-md-8"><select name="items[0][ingredient_id]" class="form-select" required><option value="">— bahan —</option>@foreach(\App\Models\Ingredient::active()->get() as $i)<option value="{{ $i->id }}">{{ $i->code }} — {{ $i->name }}</option>@endforeach</select></div>
<div class="col-md-4"><input name="items[0][qty]" type="number" step="0.001" min="0.001" class="form-control" placeholder="qty" required/></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addRfq()">+ Tambah item</button></div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Kirim RFQ</button></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let rfi = 1;
function addRfq() {
document.getElementById('rfq-items').insertAdjacentHTML('beforeend', `<div class="col-md-8"><select name="items[${rfi}][ingredient_id]" class="form-select">@foreach(\App\Models\Ingredient::active()->get() as $i)<option value="{{ $i->id }}">{{ $i->code }} — {{ $i->name }}</option>@endforeach</select></div><div class="col-md-4"><input name="items[${rfi}][qty]" type="number" step="0.001" min="0.001" class="form-control" placeholder="qty"/></div>`);
rfi++;
}
</script>
@endpush

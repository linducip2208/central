@extends('layouts.app')
@section('title', 'Buat Menu')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('menus.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Nama menu *</label><input name="name" class="form-control" value="{{ old('name') }}" required placeholder="cth. Menu Senin"/></div>
<div class="col-md-2"><label class="form-label">Tanggal *</label><input name="menu_date" type="date" class="form-control" value="{{ old('menu_date', now()->toDateString()) }}" required/></div>
<div class="col-md-2"><label class="form-label">Tipe *</label>
<select name="meal_type" class="form-select">@foreach(['BREAKFAST','LUNCH','SNACK'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Porsi rencana *</label><input name="planned_portions" type="number" min="1" class="form-control" value="{{ old('planned_portions') }}" required/></div>
<div class="col-md-2"><label class="form-label">Budget/porsi</label><input name="budget_per_portion" type="number" min="0" class="form-control" value="{{ old('budget_per_portion', 15000) }}"/></div>
<div class="col-md-6"><label class="form-label">Central kitchen</label>
<select name="central_kitchen_id" class="form-select"><option value="">—</option>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Catatan</label><input name="notes" class="form-control" value="{{ old('notes') }}"/></div>
</div>
<h4 class="mt-4">Produk dalam menu *</h4>
<div id="menu-products" class="row g-2">
<div class="col-md-7"><select name="products[0][id]" class="form-select" required><option value="">— pilih produk —</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="col-md-3"><input name="products[0][qty]" type="number" step="0.001" min="0.01" value="1" class="form-control" placeholder="qty/porsi"/></div>
<div class="col-md-2"><button type="button" class="btn btn-white w-100" onclick="addRow()">+ Baris</button></div>
</div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan menu</button><a href="{{ route('menus.index') }}" class="btn btn-white">Batal</a></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
let mi = 1;
function addRow() {
const wrap = document.getElementById('menu-products');
const div = document.createElement('div');
div.className = 'col-md-7';
div.innerHTML = `<select name="products[${mi}][id]" class="form-select">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>`;
const div2 = document.createElement('div');
div2.className = 'col-md-3';
div2.innerHTML = `<input name="products[${mi}][qty]" type="number" step="0.001" min="0.01" value="1" class="form-control"/>`;
const div3 = document.createElement('div');
div3.className = 'col-md-2';
wrap.append(div, div2, div3);
mi++;
}
</script>
@endpush

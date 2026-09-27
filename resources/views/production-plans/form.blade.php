@extends('layouts.app')
@section('title', 'Buat Rencana Produksi')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('production-plans.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Menu (item otomatis dipecah)</label>
<select name="menu_id" class="form-select"><option value="">— tanpa menu —</option>@foreach($menus as $m)<option value="{{ $m->id }}">{{ $m->name }} · {{ $m->menu_date }} ({{ $m->items->count() }} produk)</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Tanggal *</label><input name="plan_date" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-md-2"><label class="form-label">Target porsi *</label><input name="target_portions" type="number" min="1" class="form-control" required/></div>
<div class="col-md-12"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan</button></div>
</form>
</div></div>
@endsection

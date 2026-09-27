@extends('layouts.app')
@section('title', $recipe->name)
@section('subtitle', $recipe->code . ' · ' . ($recipe->product->name ?? '') . ' · yield ' . $recipe->yield_qty)
@section('actions')
<form method="POST" action="{{ route('recipes.toggle', $recipe) }}" class="d-inline">@csrf<button class="btn btn-white" type="submit">{{ $recipe->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
<form method="POST" action="{{ route('recipes.destroy', $recipe) }}" class="d-inline" onsubmit="return confirm('Hapus resep?')">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Hapus</button></form>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Komposisi (per {{ $recipe->yield_qty }} hasil)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Qty</th><th>Satuan</th><th class="text-end">Susut</th><th class="text-end">Est. biaya</th></tr></thead>
<tbody>
@php $total = 0; @endphp
@foreach($recipe->items as $it)
@php $cost = $it->qty * ($it->ingredient->standard_price ?? 0); $total += $cost; @endphp
<tr><td>{{ $it->ingredient->name ?? '-' }}</td><td class="text-end">{{ number_format($it->qty, 4) }}</td><td>{{ $it->unit->symbol ?? '' }}</td><td class="text-end">{{ $it->waste_factor_pct }}%</td><td class="text-end">{{ mbg_currency($cost) }}</td></tr>
@endforeach
<tr class="fw-bold"><td colspan="4">Estimasi biaya bahan / yield</td><td class="text-end">{{ mbg_currency($total) }}</td></tr>
</tbody></table></div></div>
@if($recipe->instructions)<div class="card mt-3"><div class="card-header"><h3 class="card-title">Instruksi</h3></div><div class="card-body">{{ $recipe->instructions }}</div></div>@endif
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Gizi per yield</h3></div>
<div class="card-body">
@if($recipe->nutrition->isNotEmpty())
@php $n = $recipe->nutrition->first(); @endphp
<dl class="row small mb-0">
<dt class="col-6">Kalori</dt><dd class="col-6">{{ $n->calories }} kkal</dd>
<dt class="col-6">Protein</dt><dd class="col-6">{{ $n->protein_g }} g</dd>
<dt class="col-6">Karbohidrat</dt><dd class="col-6">{{ $n->carbs_g }} g</dd>
<dt class="col-6">Lemak</dt><dd class="col-6">{{ $n->fat_g }} g</dd>
<dt class="col-6">Serat</dt><dd class="col-6">{{ $n->fiber_g }} g</dd>
<dt class="col-6">Natrium</dt><dd class="col-6">{{ $n->sodium_mg }} mg</dd>
</dl>
@else
<form method="POST" action="{{ route('recipes.nutrition', $recipe) }}">@csrf
<div class="row g-2">
<div class="col-6"><input name="calories" type="number" step="0.01" class="form-control" placeholder="Kalori (kkal)"/></div>
<div class="col-6"><input name="protein_g" type="number" step="0.01" class="form-control" placeholder="Protein (g)"/></div>
<div class="col-6"><input name="carbs_g" type="number" step="0.01" class="form-control" placeholder="Karbo (g)"/></div>
<div class="col-6"><input name="fat_g" type="number" step="0.01" class="form-control" placeholder="Lemak (g)"/></div>
<div class="col-6"><input name="fiber_g" type="number" step="0.01" class="form-control" placeholder="Serat (g)"/></div>
<div class="col-6"><input name="serving_size_g" type="number" step="0.01" class="form-control" placeholder="Sajian (g)"/></div>
</div>
<button class="btn btn-primary btn-sm mt-2" type="submit">Simpan gizi</button>
</form>
@endif
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-body">
<dl class="row small">
<dt class="col-6">Versi</dt><dd class="col-6">{{ $recipe->version }}</dd>
<dt class="col-6">Waktu masak</dt><dd class="col-6">{{ $recipe->cook_time_minutes }} mnt</dd>
<dt class="col-6">Status</dt><dd class="col-6">@if($recipe->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">NONAKTIF</span>@endif</dd>
</dl>
</div></div>
</div>
</div>
@endsection

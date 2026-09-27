@extends('layouts.app')
@section('title', $menu->name)
@section('subtitle', $menu->code . ' · ' . \Carbon\Carbon::parse($menu->menu_date)->format('d M Y') . ' · ' . number_format($menu->planned_portions) . ' porsi')
@section('actions')
@if($menu->status === 'DRAFT')
<form method="POST" action="{{ route('menus.status', $menu) }}" class="d-inline">@csrf<input type="hidden" name="status" value="APPROVED"/><button class="btn btn-success" type="submit">Setujui</button></form>
@endif
<form method="POST" action="{{ route('menus.status', $menu) }}" class="d-inline">@csrf<input type="hidden" name="status" value="CANCELLED"/><button class="btn btn-ghost-danger" type="submit" onclick="return confirm('Batalkan menu?')">Batalkan</button></form>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Produk ({{ $menu->items->count() }}) <span class="ms-2"><x-badge :status="$menu->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Produk</th><th class="text-end">Qty/porsi</th><th class="text-end">Total butuh</th></tr></thead>
<tbody>
@foreach($menu->items as $it)
<tr><td>{{ $it->product->name ?? '-' }}</td><td class="text-end">{{ number_format($it->qty_per_portion, 3) }}</td><td class="text-end fw-bold">{{ number_format($it->qty_per_portion * $menu->planned_portions, 0) }}</td></tr>
@endforeach
</tbody></table></div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Gizi per porsi</h3></div>
<div class="card-body">
@if($menu->nutrition->isNotEmpty())
@php $n = $menu->nutrition->first(); @endphp
<dl class="row small mb-0">
<dt class="col-6">Kalori</dt><dd class="col-6">{{ $n->calories }} kkal</dd>
<dt class="col-6">Protein</dt><dd class="col-6">{{ $n->protein_g }} g</dd>
<dt class="col-6">Karbohidrat</dt><dd class="col-6">{{ $n->carbs_g }} g</dd>
<dt class="col-6">Lemak</dt><dd class="col-6">{{ $n->fat_g }} g</dd>
<dt class="col-6">Serat</dt><dd class="col-6">{{ $n->fiber_g }} g</dd>
<dt class="col-6">Natrium</dt><dd class="col-6">{{ $n->sodium_mg }} mg</dd>
</dl>
@else
<form method="POST" action="{{ route('menus.nutrition', $menu) }}">@csrf
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
</div>
@endsection

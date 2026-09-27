@extends('layouts.app')
@section('title', $product->name)
@section('subtitle', $product->code . ' · ' . $product->category)
@section('actions')
<a href="{{ route('recipes.create') }}" class="btn btn-white">Buat resep</a>
<a href="{{ route('products.edit', $product) }}" class="btn btn-white">Ubah</a>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
@if($product->description)<p class="text-secondary">{{ $product->description }}</p>@endif
<dl class="row small">
<dt class="col-5">Porsi</dt><dd class="col-7">{{ number_format($product->portion_size_gram) }} g</dd>
<dt class="col-5">Stok jadi</dt><dd class="col-7 fw-bold">{{ number_format($stocks->sum('qty'), 0) }} {{ $product->unit->symbol ?? '' }}</dd>
<dt class="col-5">Resep aktif</dt><dd class="col-7">{{ $product->activeRecipe->name ?? '— belum ada —' }} {{ $product->activeRecipe ? '(v'.$product->activeRecipe->version.')' : '' }}</dd>
</dl>
<form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Resep ({{ $product->recipes->count() }})</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Yield</th><th>Aktif</th><th></th></tr></thead>
<tbody>
@forelse($product->recipes as $r)
<tr><td class="text-secondary">{{ $r->code }}</td><td><a href="{{ route('recipes.show', $r) }}">{{ $r->name }}</a></td><td>{{ $r->yield_qty }}</td><td>@if($r->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">NONAKTIF</span>@endif</td><td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('recipes.show', $r) }}">Buka</a></td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-3">Belum ada resep.</td></tr>@endforelse
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Stok produk jadi</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Batch</th><th class="text-end">Qty</th></tr></thead>
<tbody>
@forelse($stocks as $s)
<tr><td>{{ $s->warehouse->name ?? '-' }}</td><td>{{ $s->batch->batch_no ?? '-' }}</td><td class="text-end">{{ number_format($s->qty, 0) }}</td></tr>
@empty<tr><td colspan="3" class="text-center text-secondary py-3">Tidak ada stok.</td></tr>@endforelse
</tbody></table></div></div>
</div>
</div>
@endsection

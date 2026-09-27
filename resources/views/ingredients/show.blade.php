@extends('layouts.app')
@section('title', $ingredient->name)
@section('subtitle', $ingredient->code . ' · ' . $ingredient->category . ' · per ' . ($ingredient->unit->symbol ?? ''))
@section('actions')
<a href="{{ route('ingredients.edit', $ingredient) }}" class="btn btn-white">Ubah</a>
<a href="{{ route('ingredients.index') }}" class="btn btn-ghost-secondary">Kembali</a>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<dl class="row small">
<dt class="col-5">Harga standar</dt><dd class="col-7">{{ mbg_currency($ingredient->standard_price) }}</dd>
<dt class="col-5">Min / Max stok</dt><dd class="col-7">{{ number_format($ingredient->min_stock, 2) }} / {{ number_format($ingredient->max_stock, 2) }}</dd>
<dt class="col-5">Daya simpan</dt><dd class="col-7">{{ $ingredient->shelf_life_days }} hari</dd>
<dt class="col-5">Total stok</dt><dd class="col-7 fw-bold">{{ number_format($stocks->sum('qty'), 2) }} {{ $ingredient->unit->symbol ?? '' }}</dd>
</dl>
<form method="POST" action="{{ route('ingredients.destroy', $ingredient) }}" onsubmit="return confirm('Hapus bahan ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Stok per gudang & batch</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Batch</th><th>Expired</th><th class="text-end">Qty</th><th class="text-end">Tertahan</th></tr></thead>
<tbody>
@forelse($stocks as $s)
<tr><td>{{ $s->warehouse->name ?? '-' }}</td><td>{{ $s->batch->batch_no ?? '-' }}</td><td class="text-secondary">{{ $s->batch->expiry_date ?? '-' }}</td><td class="text-end">{{ number_format($s->qty, 2) }}</td><td class="text-end text-secondary">{{ number_format($s->reserved_qty, 2) }}</td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-3">Tidak ada stok.</td></tr>@endforelse
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Mutasi terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Tipe</th><th>Ref</th><th class="text-end">Qty</th></tr></thead>
<tbody>
@forelse($movements as $m)
<tr><td class="text-secondary">{{ $m->movement_date }}</td><td><span class="badge bg-blue-lt">{{ $m->movement_type }}</span></td><td class="text-secondary">{{ $m->reference_no ?? '-' }}</td><td class="text-end {{ $m->direction === 'IN' ? 'text-green' : 'text-red' }}">{{ $m->direction === 'IN' ? '+' : '-' }}{{ number_format($m->qty, 2) }}</td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Belum ada mutasi.</td></tr>@endforelse
</tbody></table></div></div>
</div>
</div>
@endsection

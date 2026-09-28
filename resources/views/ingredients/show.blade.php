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
<dt class="col-5">ROP / Safety</dt><dd class="col-7">{{ number_format($ingredient->reorder_point, 2) }} / {{ number_format($ingredient->safety_stock, 2) }}</dd>
<dt class="col-5">Lead / MOQ</dt><dd class="col-7">{{ $ingredient->lead_time_days }} hari / {{ number_format($ingredient->moq, 2) }}</dd>
<dt class="col-5">Supplier pref.</dt><dd class="col-7">{{ $ingredient->preferredSupplier->name ?? 'otomatis termurah' }}</dd>
<dt class="col-5">Alergen</dt><dd class="col-7">@forelse($ingredient->allergens as $a)<span class="badge bg-red-lt me-1">{{ $a->name }}</span>@empty — @endforelse</dd>
<dt class="col-5">Daya simpan</dt><dd class="col-7">{{ $ingredient->shelf_life_days }} hari</dd>
<dt class="col-5">Total stok</dt><dd class="col-7 fw-bold">{{ number_format($stocks->sum('qty'), 2) }} {{ $ingredient->unit->symbol ?? '' }}</dd>
</dl>
<form method="POST" action="{{ route('ingredients.apply-safety', $ingredient) }}" class="d-inline">@csrf<button class="btn btn-white btn-sm" type="submit" title="Hitung dari konsumsi rata-rata × lead time">Hitung safety ulang</button></form>
<form method="POST" action="{{ route('ingredients.destroy', $ingredient) }}" class="d-inline" onsubmit="return confirm('Hapus bahan ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Alternatif pengganti (approved)</h3></div>
<div class="list-group list-group-flush">
@forelse($ingredient->substitutions as $s)
<div class="list-group-item d-flex justify-content-between align-items-center">
<div><div class="fw-bold">{{ $s->substitute->name ?? '#' }} <span class="text-secondary">× {{ $s->ratio }}</span></div>
<div class="text-secondary small">{{ $s->notes ?? '' }}</div></div>
<div class="d-flex gap-1">
@if(!$s->is_approved)<form method="POST" action="{{ route('ingredients.substitutions.approve', [$ingredient, $s]) }}">@csrf<button class="btn btn-sm btn-success" type="submit">Setujui</button></form>@else<span class="badge bg-green-lt">APPROVED</span>@endif
<form method="POST" action="{{ route('ingredients.substitutions.destroy', [$ingredient, $s]) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form>
</div>
</div>
@empty<div class="list-group-item text-secondary">Belum ada alternatif.</div>@endforelse
</div>
<div class="card-body border-top">
<form method="POST" action="{{ route('ingredients.substitutions.store', $ingredient) }}">@csrf
<div class="row g-1">
<div class="col-6"><select name="substitute_id" class="form-select form-select-sm" required><option value="">— pengganti —</option>@foreach(\App\Models\Ingredient::active()->where('id', '!=', $ingredient->id)->get() as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach</select></div>
<div class="col-3"><input name="ratio" type="number" step="0.0001" min="0.0001" value="1" class="form-control form-control-sm" title="rasio"/></div>
<div class="col-3"><button class="btn btn-sm btn-white w-100" type="submit">Tambah</button></div>
</div>
</form>
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

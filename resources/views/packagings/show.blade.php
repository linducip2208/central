@extends('layouts.app')
@section('title', 'Packaging ' . $pkg->number)
@section('actions')
@if($pkg->status === 'DRAFT')
<form method="POST" action="{{ route('packagings.complete', $pkg) }}" class="d-inline">@csrf
<div class="input-group"><input name="packages_done" type="number" min="1" max="{{ $pkg->packages_planned }}" value="{{ $pkg->packages_planned }}" class="form-control" required/><button class="btn btn-success" type="submit">Selesaikan</button></div>
</form>
@endif
@endsection
@section('content')
<div class="card"><div class="card-body">
<dl class="row small">
<dt class="col-3">Status</dt><dd class="col-9"><x-badge :status="$pkg->status"/></dd>
<dt class="col-3">Dari WO</dt><dd class="col-9">{{ $pkg->productionOrder->number ?? '-' }}</dd>
<dt class="col-3">Rencana / selesai</dt><dd class="col-9">{{ number_format($pkg->packages_planned) }} / {{ number_format($pkg->packages_done) }} {{ $pkg->package_type }}</dd>
</dl>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Produk</th><th class="text-end">Terkemas</th></tr></thead>
<tbody>
@foreach($pkg->items as $it)
<tr><td>{{ $it->product->name ?? '-' }}</td><td class="text-end">{{ number_format($it->qty_packed) }}</td></tr>
@endforeach
</tbody></table></div>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Material kemasan terpakai</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Material</th><th>Batch</th><th class="text-end">Qty</th></tr></thead>
<tbody>
@forelse($pkg->materialUsages as $mu)
<tr><td>{{ $mu->ingredient->name ?? '' }}</td><td class="text-secondary">{{ $mu->batch->batch_no ?? '-' }}</td><td class="text-end">{{ number_format($mu->qty_used, 2) }}</td></tr>
@empty<tr><td colspan="3" class="text-center text-secondary py-2">Belum ada pemakaian material.</td></tr>@endforelse
</tbody></table></div>
@if($materials->isNotEmpty())
<div class="card-body border-top">
<form method="POST" action="{{ route('packagings.materials', $pkg) }}">@csrf
<div class="row g-1">
<div class="col-6"><select name="ingredient_id" class="form-select form-select-sm" required>@foreach($materials as $m)<option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit->symbol ?? '' }})</option>@endforeach</select></div>
<div class="col-4"><input name="qty" type="number" step="0.001" min="0.001" class="form-control form-control-sm" placeholder="qty *" required/></div>
<div class="col-2"><button class="btn btn-sm btn-white w-100" type="submit">Pakai</button></div>
</div>
</form>
</div>
@else
<div class="card-body border-top"><p class="text-secondary small mb-0">Belum ada bahan kategori PACKAGING. Tambahkan di master bahan.</p></div>
@endif
</div>
@endsection

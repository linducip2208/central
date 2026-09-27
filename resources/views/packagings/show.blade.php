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
@endsection

@extends('layouts.app')
@section('title', 'GR ' . $gr->number)
@section('subtitle', 'PO: ' . ($gr->purchaseOrder->number ?? '-') . ' · Gudang: ' . ($gr->warehouse->name ?? '-'))
@section('actions')<a href="{{ route('goods-receipts.index') }}" class="btn btn-ghost-secondary">Kembali</a>@endsection
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Item diterima <span class="ms-2"><x-badge :status="$gr->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Diterima</th><th class="text-end">Reject</th><th>Batch</th><th>Expired</th></tr></thead>
<tbody>
@foreach($gr->items as $it)
<tr><td>{{ $it->ingredient->name ?? '-' }}</td><td class="text-end">{{ number_format($it->qty_received, 2) }}</td><td class="text-end">{{ number_format($it->qty_rejected, 2) }}</td><td>{{ $it->batch_no ?? '-' }}</td><td class="text-secondary">{{ $it->expiry_date ?? '-' }}</td></tr>
@endforeach
</tbody></table></div></div>
@endsection

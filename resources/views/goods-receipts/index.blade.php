@extends('layouts.app')
@section('title', 'Goods Receipt')
@section('actions')<a href="{{ route('goods-receipts.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Terima barang</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['RECEIVED','PARTIAL','COMPLETED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>PO</th><th>Supplier</th><th>Gudang</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($grs as $gr)
<tr>
<td><a href="{{ route('goods-receipts.show', $gr) }}">{{ $gr->number }}</a></td>
<td class="text-secondary">{{ $gr->receipt_date }}</td>
<td class="text-secondary">{{ $gr->purchaseOrder->number ?? '-' }}</td>
<td class="text-secondary">{{ $gr->supplier->name ?? '-' }}</td>
<td class="text-secondary">{{ $gr->warehouse->name ?? '-' }}</td>
<td><x-badge :status="$gr->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('goods-receipts.show', $gr) }}">Detail</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada penerimaan"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $grs->links() }}</div>
</div></div>
@endsection

@extends('layouts.app')
@section('title', 'Purchase Order')
@section('actions')<a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>PO</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','SUBMITTED','APPROVED','REJECTED','PARTIAL','COMPLETED','CANCELLED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Supplier</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($pos as $po)
<tr>
<td><a href="{{ route('purchase-orders.show', $po) }}">{{ $po->number }}</a></td>
<td class="text-secondary">{{ $po->order_date }}</td>
<td class="text-secondary">{{ $po->supplier->name ?? '-' }}</td>
<td class="text-end">{{ mbg_currency($po->grand_total) }}</td>
<td><x-badge :status="$po->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('purchase-orders.show', $po) }}">Proses</a></td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada PO"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $pos->links() }}</div>
</div></div>
@endsection

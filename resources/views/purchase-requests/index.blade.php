@extends('layouts.app')
@section('title', 'Purchase Request')
@section('actions')<a href="{{ route('purchase-requests.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>PR</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','SUBMITTED','APPROVED','REJECTED','ORDERED','PARTIAL','COMPLETED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Gudang</th><th class="text-end">Item</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($prs as $pr)
<tr>
<td><a href="{{ route('purchase-requests.show', $pr) }}">{{ $pr->number }}</a></td>
<td class="text-secondary">{{ $pr->request_date }}</td>
<td class="text-secondary">{{ $pr->warehouse->name ?? '-' }}</td>
<td class="text-end">{{ $pr->items_count ?? $pr->items()->count() }}</td>
<td><x-badge :status="$pr->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('purchase-requests.show', $pr) }}">Proses</a></td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada PR"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $prs->links() }}</div>
</div></div>
@endsection

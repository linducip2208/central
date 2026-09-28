@extends('layouts.app')
@section('title', 'RFQ & Quotation')
@section('actions')<a href="{{ route('rfqs.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>RFQ</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','SENT','AWARDED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th class="text-end">Supplier diundang</th><th class="text-end">Penawaran</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($rfqs as $r)
<tr>
<td><a href="{{ route('rfqs.show', $r) }}">{{ $r->number }}</a></td>
<td class="text-secondary">{{ $r->rfq_date }}</td>
<td class="text-end">{{ $r->suppliers_count ?? $r->suppliers()->count() }}</td>
<td class="text-end">{{ $r->quotations_count ?? $r->quotations()->count() }}</td>
<td><x-badge :status="$r->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('rfqs.show', $r) }}">Banding</a></td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada RFQ"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $rfqs->links() }}</div>
</div></div>
@endsection

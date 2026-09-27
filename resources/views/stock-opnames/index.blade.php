@extends('layouts.app')
@section('title', 'Stock Opname')
@section('actions')<a href="{{ route('stock-opnames.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Opname baru</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','COUNTED','APPROVED','POSTED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Gudang</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($opnames as $o)
<tr>
<td><a href="{{ route('stock-opnames.show', $o) }}">{{ $o->number }}</a></td>
<td class="text-secondary">{{ $o->opname_date }}</td>
<td class="text-secondary">{{ $o->warehouse->name ?? '-' }}</td>
<td><x-badge :status="$o->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('stock-opnames.show', $o) }}">Proses</a></td>
</tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada opname"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $opnames->links() }}</div>
</div></div>
@endsection

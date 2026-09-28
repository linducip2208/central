@extends('layouts.app')
@section('title', 'Recall Management')
@section('actions')<a href="{{ route('recalls.create') }}" class="btn btn-danger"><i class="ti ti-plus me-1"></i>Recall</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','ACTIVE','CONTAINED','CLOSED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Alasan</th><th>Severity</th><th>Pemicu</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($recalls as $r)
<tr>
<td><a href="{{ route('recalls.show', $r) }}">{{ $r->number }}</a></td>
<td>{{ $r->reason }}</td>
<td>{{ $r->severity }}</td>
<td class="text-secondary">{{ $r->triggerBatch->batch_no ?? '' }}</td>
<td><x-badge :status="$r->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('recalls.show', $r) }}">Kelola</a></td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada recall"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $recalls->links() }}</div>
</div></div>
@endsection

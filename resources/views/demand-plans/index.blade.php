@extends('layouts.app')
@section('title', 'Demand Plans')
@section('actions')<a href="{{ route('demand-plans.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Rencana</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','APPROVED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Periode</th><th>Tanggal</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($plans as $p)
<tr>
<td><a href="{{ route('demand-plans.show', $p) }}">{{ $p->number }}</a></td>
<td>{{ $p->period_type }}</td>
<td class="text-secondary">{{ $p->period_start }} s.d. {{ $p->period_end }}</td>
<td><x-badge :status="$p->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('demand-plans.show', $p) }}">Buka</a></td>
</tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada demand plan"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $plans->links() }}</div>
</div></div>
@endsection

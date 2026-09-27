@extends('layouts.app')
@section('title', 'Rencana Produksi')
@section('actions')<a href="{{ route('production-plans.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Rencana</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','APPROVED','RELEASED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Menu</th><th class="text-end">Target porsi</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($plans as $p)
<tr>
<td><a href="{{ route('production-plans.show', $p) }}">{{ $p->number }}</a></td>
<td class="text-secondary">{{ $p->plan_date }}</td>
<td class="text-secondary">{{ $p->menu->name ?? '-' }}</td>
<td class="text-end">{{ number_format($p->target_portions) }}</td>
<td><x-badge :status="$p->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('production-plans.show', $p) }}">Proses</a></td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada rencana"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $plans->links() }}</div>
</div></div>
@endsection

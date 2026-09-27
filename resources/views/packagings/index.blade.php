@extends('layouts.app')
@section('title', 'Packaging')
@section('actions')<a href="{{ route('packagings.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Packaging</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','COMPLETED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Dari WO</th><th class="text-end">Rencana</th><th class="text-end">Selesai</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($pkgs as $p)
<tr>
<td><a href="{{ route('packagings.show', $p) }}">{{ $p->number }}</a></td>
<td class="text-secondary">{{ $p->packaging_date }}</td>
<td class="text-secondary">{{ $p->productionOrder->number ?? '-' }}</td>
<td class="text-end">{{ number_format($p->packages_planned) }}</td>
<td class="text-end">{{ number_format($p->packages_done) }}</td>
<td><x-badge :status="$p->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('packagings.show', $p) }}">Proses</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada packaging"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $pkgs->links() }}</div>
</div></div>
@endsection

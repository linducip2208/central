@extends('layouts.app')
@section('title', 'Distribusi')
@section('actions')<a href="{{ route('distributions.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Distribusi</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['PLANNED','IN_TRANSIT','COMPLETED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Kendaraan</th><th>Sopir</th><th class="text-end">Porsi</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($dists as $d)
<tr>
<td><a href="{{ route('distributions.show', $d) }}">{{ $d->number }}</a></td>
<td class="text-secondary">{{ $d->distribution_date }}</td>
<td class="text-secondary">{{ $d->vehicle_no ?? '-' }}</td>
<td class="text-secondary">{{ $d->driver_name ?? '-' }}</td>
<td class="text-end">{{ number_format($d->total_portions) }}</td>
<td><x-badge :status="$d->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('distributions.show', $d) }}">Proses</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada distribusi"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $dists->links() }}</div>
</div></div>
@endsection

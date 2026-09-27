@extends('layouts.app')
@section('title', 'Delivery')
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['PLANNED','IN_TRANSIT','DELIVERED','PARTIAL','FAILED','RETURNED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Sekolah</th><th class="text-end">Rencana</th><th class="text-end">Terkirim</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($deliveries as $d)
<tr>
<td><a href="{{ route('deliveries.show', $d) }}">{{ $d->number }}</a></td>
<td class="text-secondary">{{ $d->delivery_date }}</td>
<td>{{ $d->school->name ?? '-' }}</td>
<td class="text-end">{{ number_format($d->qty_planned) }}</td>
<td class="text-end">{{ number_format($d->qty_delivered) }}</td>
<td><x-badge :status="$d->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('deliveries.show', $d) }}">Proses</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada delivery"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $deliveries->links() }}</div>
</div></div>
@endsection

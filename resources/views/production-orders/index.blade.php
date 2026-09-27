@extends('layouts.app')
@section('title', 'Production Order')
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['PLANNED','RELEASED','IN_PROGRESS','PARTIAL','COMPLETED','CANCELLED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Produk</th><th class="text-end">Rencana</th><th class="text-end">Hasil</th><th>Progress</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($orders as $o)
<tr>
<td><a href="{{ route('production-orders.show', $o) }}">{{ $o->number }}</a></td>
<td class="text-secondary">{{ $o->production_date }}</td>
<td>{{ $o->product->name ?? '-' }}</td>
<td class="text-end">{{ number_format($o->planned_qty, 0) }}</td>
<td class="text-end">{{ number_format($o->produced_qty, 0) }}</td>
<td style="min-width:120px"><div class="progress"><div class="progress-bar bg-green" style="width: {{ min(100, $o->completionPct()) }}%"></div></div></td>
<td><x-badge :status="$o->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('production-orders.show', $o) }}">Proses</a></td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada production order"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $orders->links() }}</div>
</div></div>
@endsection

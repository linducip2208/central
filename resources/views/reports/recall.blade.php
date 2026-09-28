@extends('layouts.app')
@section('title', 'Laporan Recall')
@section('content')
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Alasan</th><th class="text-end">Batch</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($recalls as $r)
<tr><td><a href="{{ route('recalls.show', $r) }}">{{ $r->number }}</a></td><td>{{ $r->reason }}</td><td class="text-end">{{ $r->items_count }}</td><td><x-badge :status="$r->status"/></td><td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('recalls.show', $r) }}">Buka</a></td></tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada recall"/></td></tr>@endforelse
</tbody></table></div>
</div></div>
@endsection

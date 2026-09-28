@extends('layouts.app')
@section('title', 'NCR / CAPA')
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['OPEN','CLOSED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Kategori</th><th>Severity</th><th>Disposisi</th><th class="text-end">CAPA open</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($ncrs as $n)
<tr>
<td><a href="{{ route('ncrs.show', $n) }}">{{ $n->number }}</a></td>
<td>{{ $n->category }}</td>
<td>@if($n->severity === 'CRITICAL')<span class="badge bg-red-lt">CRITICAL</span>@elseif($n->severity === 'MAJOR')<span class="badge bg-yellow-lt">MAJOR</span>@else<span class="badge bg-secondary-lt">MINOR</span>@endif</td>
<td>{{ $n->disposition }}</td>
<td class="text-end">{{ $n->capa_actions_count ?? $n->capaActions()->where('status', '!=', 'DONE')->count() }}</td>
<td><x-badge :status="$n->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('ncrs.show', $n) }}">Kelola</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada NCR"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $ncrs->links() }}</div>
</div></div>
@endsection

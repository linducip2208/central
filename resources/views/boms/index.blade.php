@extends('layouts.app')
@section('title', 'Bill of Materials')
@section('actions')<a href="{{ route('boms.compare') }}" class="btn btn-white">Banding + simulasi</a><a href="{{ route('boms.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>BOM</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','ACTIVE','ARCHIVED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Produk</th><th>Ver</th><th>Berlaku</th><th>Yield</th><th class="text-end">Komponen</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($boms as $b)
<tr>
<td><a href="{{ route('boms.show', $b) }}">{{ $b->code }}</a></td>
<td class="text-secondary">{{ \App\Models\Product::whereKey($b->item_id)->value('name') }}</td>
<td>{{ $b->version }}</td>
<td class="text-secondary">{{ $b->effective_from ?? '-' }}</td>
<td>{{ $b->yield_qty }}</td>
<td class="text-end">{{ $b->items->count() }}</td>
<td><x-badge :status="$b->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('boms.show', $b) }}">Buka</a></td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada BOM"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $boms->links() }}</div>
</div></div>
@endsection

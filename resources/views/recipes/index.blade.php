@extends('layouts.app')
@section('title', 'Resep')
@section('actions')<a href="{{ route('recipes.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Resep</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Produk</th><th>Yield</th><th>Versi</th><th>Aktif</th><th></th></tr></thead>
<tbody>
@forelse($recipes as $r)
<tr>
<td class="text-secondary">{{ $r->code }}</td>
<td><a href="{{ route('recipes.show', $r) }}">{{ $r->name }}</a></td>
<td class="text-secondary">{{ $r->product->name ?? '-' }}</td>
<td>{{ $r->yield_qty }}</td>
<td class="text-secondary">{{ $r->version }}</td>
<td>@if($r->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">NONAKTIF</span>@endif</td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('recipes.show', $r) }}">Detail</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada resep"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $recipes->links() }}</div>
</div></div>
@endsection

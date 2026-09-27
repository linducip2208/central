@extends('layouts.app')
@section('title', 'Bahan Baku')
@section('actions')<a href="{{ route('ingredients.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Bahan</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Satuan</th><th class="text-end">Harga std</th><th class="text-end">Min. stok</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($ingredients as $i)
<tr>
<td class="text-secondary">{{ $i->code }}</td>
<td><a href="{{ route('ingredients.show', $i) }}">{{ $i->name }}</a></td>
<td class="text-secondary">{{ $i->category }}</td>
<td>{{ $i->unit->symbol ?? '-' }}</td>
<td class="text-end">{{ mbg_currency($i->standard_price) }}</td>
<td class="text-end">{{ number_format($i->min_stock, 2) }}</td>
<td>@if($i->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">NONAKTIF</span>@endif</td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('ingredients.show', $i) }}">Detail</a></td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada bahan baku"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $ingredients->links() }}</div>
</div></div>
@endsection

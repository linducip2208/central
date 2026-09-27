@extends('layouts.app')
@section('title', 'Produk')
@section('actions')<a href="{{ route('products.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Produk</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Satuan</th><th class="text-end">Porsi (g)</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($products as $p)
<tr>
<td class="text-secondary">{{ $p->code }}</td>
<td><a href="{{ route('products.show', $p) }}">{{ $p->name }}</a></td>
<td class="text-secondary">{{ $p->category }}</td>
<td>{{ $p->unit->symbol ?? '-' }}</td>
<td class="text-end">{{ number_format($p->portion_size_gram) }}</td>
<td>@if($p->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">NONAKTIF</span>@endif</td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('products.show', $p) }}">Detail</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada produk"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $products->links() }}</div>
</div></div>
@endsection

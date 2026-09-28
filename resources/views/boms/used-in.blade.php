@extends('layouts.app')
@section('title', 'Reverse BOM: ' . $ingredient->name)
@section('subtitle', 'BOM/produk apa saja yang memakai bahan ini')
@section('content')
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>BOM</th><th>Produk induk</th><th>Level</th><th class="text-end">Qty</th><th>Status</th></tr></thead>
<tbody>
@forelse($rows as $r)
<tr>
<td><a href="{{ route('boms.show', $r->bom) }}">{{ $r->bom->code }}</a></td>
<td class="text-secondary">{{ \App\Models\Product::whereKey($r->bom->item_id)->value('name') }}</td>
<td>{{ $r->level }}</td>
<td class="text-end">{{ number_format($r->qty, 4) }}</td>
<td><x-badge :status="$r->bom->status"/></td>
</tr>
@empty<tr><td colspan="5"><x-empty title="Tidak dipakai BOM mana pun"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
@endsection

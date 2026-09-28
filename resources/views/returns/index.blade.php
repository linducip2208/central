@extends('layouts.app')
@section('title', 'Retur ke Dapur')
@section('content')
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>ID</th><th>Delivery</th><th>Produk</th><th class="text-end">Qty</th><th>Alasan</th><th>Kondisi</th><th>Disposisi</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($returns as $r)
<tr>
<td class="text-secondary">RTN-{{ $r->id }}</td>
<td><a href="{{ route('deliveries.show', $r->delivery) }}">{{ $r->delivery->number ?? '' }}</a><div class="text-secondary small">{{ $r->delivery->school->name ?? '' }}</div></td>
<td>{{ $r->product->name ?? '' }}</td>
<td class="text-end">{{ number_format($r->qty) }}</td>
<td class="text-secondary">{{ $r->reason }}</td>
<td>@if($r->condition === 'GOOD')<span class="badge bg-green-lt">GOOD</span>@else<span class="badge bg-red-lt">{{ $r->condition }}</span>@endif</td>
<td>{{ $r->disposition }}</td>
<td><x-badge :status="$r->status"/></td>
<td class="text-end d-flex gap-1 justify-content-end">
@if($r->disposition === 'PENDING' && $r->condition === 'GOOD')
<form method="POST" action="{{ route('returns.restock', $r) }}">@csrf<button class="btn btn-sm btn-success" type="submit">Restock</button></form>
@endif
@if($r->disposition === 'PENDING')
<form method="POST" action="{{ route('returns.waste', $r) }}">@csrf<button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Catat sebagai waste?')">Waste</button></form>
@endif
</td>
</tr>
@empty<tr><td colspan="9"><x-empty title="Belum ada retur"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $returns->links() }}</div>
</div></div>
@endsection

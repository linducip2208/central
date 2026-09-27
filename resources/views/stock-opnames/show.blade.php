@extends('layouts.app')
@section('title', 'Opname ' . $opname->number)
@section('subtitle', ($opname->warehouse->name ?? '-') . ' · ' . $opname->opname_date)
@section('actions')
@if($opname->status === 'COUNTED')
<form method="POST" action="{{ route('stock-opnames.approve', $opname) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Setujui</button></form>
@endif
@if($opname->status === 'APPROVED')
<form method="POST" action="{{ route('stock-opnames.post', $opname) }}" class="d-inline" onsubmit="return confirm('Posting selisih ke ledger? Tidak dapat dibatalkan.')">@csrf<button class="btn btn-warning" type="submit">Posting ke stok</button></form>
@endif
@endsection
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Hasil hitung <span class="ms-2"><x-badge :status="$opname->status"/></span></h3></div>
<div class="card-body">
@if($opname->isEditable())
<form method="POST" action="{{ route('stock-opnames.count', $opname) }}">@csrf
@endif
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Item</th><th>Batch</th><th class="text-end">Sistem</th><th class="text-end">Fisik</th><th class="text-end">Selisih</th></tr></thead>
<tbody>
@foreach($opname->items as $it)
@php
$name = $it->item_type === 'product' ? ($pnames[$it->item_id] ?? 'Produk #'.$it->item_id) : ($names[$it->item_id] ?? 'Bahan #'.$it->item_id);
$diff = $it->physical_qty - $it->system_qty;
@endphp
<tr>
<td>{{ $name }} <span class="text-secondary small">{{ $it->item_type }}</span></td>
<td class="text-secondary">{{ $it->batch->batch_no ?? '-' }}</td>
<td class="text-end text-secondary">{{ number_format($it->system_qty, 2) }}</td>
<td class="text-end" style="min-width:140px">
@if($opname->isEditable())
<input name="counts[{{ $it->id }}]" type="number" step="0.001" min="0" value="{{ $it->physical_qty }}" class="form-control form-control-sm text-end"/>
@else
{{ number_format($it->physical_qty, 2) }}
@endif
</td>
<td class="text-end fw-bold {{ $diff == 0 ? 'text-secondary' : ($diff > 0 ? 'text-green' : 'text-red') }}">{{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}</td>
</tr>
@endforeach
</tbody></table></div>
@if($opname->isEditable())
<button class="btn btn-primary mt-3" type="submit">Simpan hasil hitung</button>
</form>
@endif
</div></div>
@endsection

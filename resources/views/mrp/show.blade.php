@extends('layouts.app')
@section('title', 'MRP ' . $run->number)
@section('subtitle', 'Gudang: ' . ($run->warehouse->name ?? '-') . ' · ' . $run->run_date)
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Hasil perhitungan (explainable)</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('mrp.to-pr', $run) }}">@csrf
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th></th><th>Bahan</th><th class="text-end">Gross</th><th class="text-end">Tersedia</th><th class="text-end">Incoming</th><th class="text-end">Safety</th><th class="text-end">Net</th><th class="text-end">Saran beli</th><th>Supplier</th><th>Rekomendasi</th></tr></thead>
<tbody>
@foreach($lines as $l)
<tr>
<td>@if($l->recommendation === 'PURCHASE')<input type="checkbox" name="line_ids[]" value="{{ $l->id }}" class="form-check-input" checked/>@endif</td>
<td>{{ $l->ingredient->name ?? '#' }}<div class="text-secondary small" title="{{ $l->explanation }}"><i class="ti ti-info-circle"></i> kenapa?</div></td>
<td class="text-end">{{ number_format($l->gross_requirement, 2) }}</td>
<td class="text-end">{{ number_format($l->available, 2) }}</td>
<td class="text-end">{{ number_format($l->incoming, 2) }}</td>
<td class="text-end">{{ number_format($l->safety_stock, 2) }}</td>
<td class="text-end fw-bold">{{ number_format($l->net_requirement, 2) }}</td>
<td class="text-end">{{ number_format($l->suggested_order_qty, 2) }}</td>
<td class="text-secondary small">{{ $l->suggestedSupplier->name ?? '-' }} @if($l->suggested_price)({{ mbg_currency($l->suggested_price) }})@endif</td>
<td>@if($l->recommendation === 'PURCHASE')<span class="badge bg-yellow-lt">BELI</span>@elseif($l->recommendation === 'TRANSFER')<span class="badge bg-blue-lt">TRANSFER</span>@elseif($l->recommendation === 'SHORTAGE')<span class="badge bg-red-lt">SHORTAGE</span>@elseif($l->recommendation === 'SURPLUS')<span class="badge bg-purple-lt">SURPLUS</span>@else<span class="badge bg-green-lt">CUKUP</span>@endif
@if($l->transfer_from_warehouse_id)<div class="small text-secondary">dari gudang #{{ $l->transfer_from_warehouse_id }}</div>@endif
@if($l->expiring_soon > 0)<div class="small text-yellow">~{{ number_format($l->expiring_soon, 1) }} hampir expired</div>@endif</td>
</tr>
@endforeach
</tbody></table></div>
<div class="card-body d-flex gap-2 px-0 pb-0">
<button class="btn btn-success" type="submit">Buat draft PR dari yang dicentang</button>
{{ $lines->links() }}
</div>
</form>
</div></div>
@endsection

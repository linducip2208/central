@extends('layouts.app')
@section('title', 'RFQ ' . $rfq->number)
@section('actions')
<form method="POST" action="{{ route('rfqs.award', $rfq) }}" class="d-inline" onsubmit="return confirm('Pilih pemenang termurah per item dan buat PO?')">@csrf<button class="btn btn-success" type="submit">Award → buat PO</button></form>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Perbandingan harga <span class="ms-2"><x-badge :status="$rfq->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Butuh</th>@foreach($rfq->quotations as $q)<th class="text-end">{{ $q->supplier->name }}<br/><span class="text-secondary fw-normal">{{ $q->number }} · {{ $q->status }}</span></th>@endforeach</tr></thead>
<tbody>
@foreach($rfq->items as $item)
@php
$best = null;
foreach ($rfq->quotations as $q) { $qi = $q->items->firstWhere('rfq_item_id', $item->id); if ($qi && (!$best || $qi->unit_price < $best->unit_price)) $best = $qi; }
@endphp
<tr><td>{{ $item->ingredient->name ?? '' }}</td><td class="text-end">{{ number_format($item->qty, 2) }}</td>
@foreach($rfq->quotations as $q)
@php $qi = $q->items->firstWhere('rfq_item_id', $item->id); @endphp
<td class="text-end @if($best && $qi && $qi->id === $best->id) text-green fw-bold @endif">{{ $qi ? mbg_currency($qi->unit_price) : '—' }}</td>
@endforeach
</tr>
@endforeach
<tr class="fw-bold"><td colspan="2" class="text-end">Total penawaran</td>@foreach($rfq->quotations as $q)<td class="text-end">{{ mbg_currency($q->total()) }}</td>@endforeach</tr>
</tbody></table></div></div>
</div>
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Catat penawaran supplier</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('rfqs.quotations', $rfq) }}">@csrf
<div class="row g-2">
<div class="col-md-4"><label class="form-label">Supplier *</label>
<select name="supplier_id" class="form-select" required>@foreach($rfq->suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Lead time (hari)</label><input name="lead_time_days" type="number" min="0" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
@foreach($rfq->items as $item)
<div class="col-md-8"><input class="form-control" value="{{ $item->ingredient->name }} (butuh {{ number_format($item->qty, 2) }})" disabled/></div>
<div class="col-md-4"><input name="prices[{{ $item->id }}]" type="number" step="0.01" min="0" class="form-control" placeholder="harga satuan"/></div>
@endforeach
</div>
<button class="btn btn-primary mt-2" type="submit">Simpan penawaran</button>
</form>
</div></div>
</div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'PO ' . $po->number)
@section('subtitle', ($po->supplier->name ?? '-') . ' · ' . mbg_currency($po->grand_total))
@section('actions')
@if($po->status === 'DRAFT')
<form method="POST" action="{{ route('purchase-orders.submit', $po) }}" class="d-inline">@csrf<button class="btn btn-primary" type="submit">Submit</button></form>
@endif
@if($po->status === 'SUBMITTED')
<form method="POST" action="{{ route('purchase-orders.approve', $po) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Setujui</button></form>
<form method="POST" action="{{ route('purchase-orders.reject', $po) }}" class="d-inline">@csrf<div class="input-group d-inline-flex" style="width:auto"><input name="reject_reason" class="form-control" placeholder="Alasan" required/><button class="btn btn-danger" type="submit">Tolak</button></div></form>
@endif
@if(in_array($po->status, ['APPROVED','PARTIAL']))
<a href="{{ route('goods-receipts.create') }}" class="btn btn-success">Terima barang (GR)</a>
<form method="POST" action="{{ route('purchase-orders.cancel', $po) }}" class="d-inline" onsubmit="return confirm('Batalkan PO?')">@csrf<button class="btn btn-ghost-danger" type="submit">Batal</button></form>
@endif
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Item <span class="ms-2"><x-badge :status="$po->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Dipesan</th><th class="text-end">Diterima</th><th class="text-end">Sisa</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
<tbody>
@foreach($po->items as $it)
<tr><td>{{ $it->ingredient->name ?? '-' }}</td>
<td class="text-end">{{ number_format($it->qty_ordered, 2) }}</td>
<td class="text-end">{{ number_format($it->qty_received, 2) }}</td>
<td class="text-end fw-bold">{{ number_format($it->remainingToReceive(), 2) }}</td>
<td class="text-end">{{ mbg_currency($it->unit_price) }}</td>
<td class="text-end">{{ mbg_currency($it->line_total) }}</td></tr>
@endforeach
<tr class="fw-bold"><td colspan="5" class="text-end">Grand total</td><td class="text-end">{{ mbg_currency($po->grand_total) }}</td></tr>
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Riwayat penerimaan</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor GR</th><th>Tanggal</th><th>Status</th></tr></thead>
<tbody>
@forelse($po->receipts as $gr)
<tr><td><a href="{{ route('goods-receipts.show', $gr) }}">{{ $gr->number }}</a></td><td class="text-secondary">{{ $gr->receipt_date }}</td><td><x-badge :status="$gr->status"/></td></tr>
@empty<tr><td colspan="3" class="text-center text-secondary py-3">Belum ada penerimaan.</td></tr>@endforelse
</tbody></table></div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-body">
<dl class="row small mb-0">
<dt class="col-5">Dari PR</dt><dd class="col-7">{{ $po->purchaseRequest->number ?? '-' }}</dd>
<dt class="col-5">Gudang</dt><dd class="col-7">{{ $po->warehouse->name ?? '-' }}</dd>
<dt class="col-5">Ekspektasi</dt><dd class="col-7">{{ $po->expected_date ?? '-' }}</dd>
<dt class="col-5">Pembayaran</dt><dd class="col-7">{{ $po->payment_terms }}</dd>
<dt class="col-5">Catatan</dt><dd class="col-7">{{ $po->notes ?? '-' }}</dd>
</dl>
</div></div>
</div>
</div>
@endsection

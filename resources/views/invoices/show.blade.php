@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->number)
@section('subtitle', 'Supplier: ' . ($invoice->supplier->name ?? '-') . ' · No. supplier: ' . $invoice->supplier_invoice_no)
@section('actions')
@if($invoice->status === 'DRAFT' && $invoice->isMatched())
<form method="POST" action="{{ route('invoices.verify', $invoice) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Verifikasi</button></form>
@endif
@if($invoice->status === 'VERIFIED' && $invoice->payment_status === 'UNPAID')
<form method="POST" action="{{ route('invoices.pay', $invoice) }}" class="d-inline">@csrf<button class="btn btn-primary" type="submit">Tandai lunas</button></form>
@endif
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Item tagihan</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Qty tagih</th><th class="text-end">Harga tagih</th><th class="text-end">Subtotal</th></tr></thead>
<tbody>
@foreach($invoice->items as $it)
<tr><td>{{ $it->ingredient->name ?? '' }}</td><td class="text-end">{{ number_format($it->qty, 2) }}</td><td class="text-end">{{ mbg_currency($it->unit_price) }}</td><td class="text-end">{{ mbg_currency($it->line_total) }}</td></tr>
@endforeach
<tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">{{ mbg_currency($invoice->subtotal) }}</td></tr>
<tr><td colspan="3" class="text-end">Pajak</td><td class="text-end">{{ mbg_currency($invoice->tax_amount) }}</td></tr>
<tr class="fw-bold"><td colspan="3" class="text-end">Grand total</td><td class="text-end">{{ mbg_currency($invoice->grand_total) }}</td></tr>
</tbody></table></div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">3-way match: PO + GR + Invoice</h3></div>
<div class="card-body">
<dl class="row small mb-0">
<dt class="col-5">PO</dt><dd class="col-7">{{ $invoice->purchaseOrder->number ?? '—' }}</dd>
<dt class="col-5">GR</dt><dd class="col-7">{{ $invoice->goodsReceipt->number ?? '—' }}</dd>
<dt class="col-5">Selisih qty</dt><dd class="col-7 @if(abs($invoice->qty_variance) > 0.001) text-red fw-bold @endif">{{ number_format($invoice->qty_variance, 3) }} (tagih − terima)</dd>
<dt class="col-5">Selisih harga</dt><dd class="col-7 @if(abs($invoice->price_variance) > 0.01) text-red fw-bold @endif">{{ mbg_currency($invoice->price_variance) }}</dd>
<dt class="col-5">Match</dt><dd class="col-7">@if($invoice->isMatched())<span class="badge bg-green-lt">MATCHED</span>@else<span class="badge bg-yellow-lt">VARIANCE</span>@endif</dd>
<dt class="col-5">Status</dt><dd class="col-7"><x-badge :status="$invoice->status"/> · {{ $invoice->payment_status }}</dd>
</dl>
@if(!$invoice->isMatched())
<div class="alert alert-warning mt-2 mb-0">Ada variansi — selesaikan dengan supplier sebelum verifikasi.</div>
@endif
</div></div>
</div>
</div>
@endsection

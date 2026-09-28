@extends('layouts.app')
@section('title', 'GR ' . $gr->number)
@section('subtitle', 'PO: ' . ($gr->purchaseOrder->number ?? '-') . ' · Gudang: ' . ($gr->warehouse->name ?? '-'))
@section('actions')
<button class="btn btn-white" type="button" data-bs-toggle="modal" data-bs-target="#returnModal">Retur ke supplier</button>
<a href="{{ route('goods-receipts.index') }}" class="btn btn-ghost-secondary">Kembali</a>
@endsection
@section('modal')
<div class="modal fade" id="returnModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('goods-receipts.supplier-return', $gr) }}">@csrf
<div class="modal-header"><h5 class="modal-title">Retur ke supplier</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-2"><label class="form-label">Item GR *</label>
<select name="goods_receipt_item_id" class="form-select" required>@foreach($gr->items as $it)<option value="{{ $it->id }}">{{ $it->ingredient->name ?? '' }} (terima {{ number_format($it->qty_received, 2) }})</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Qty *</label><input name="qty" type="number" step="0.001" min="0.001" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Alasan *</label><input name="reason" class="form-control" required placeholder="cth. busuk, salah kirim"/></div>
</div>
<div class="modal-footer"><button class="btn btn-danger" type="submit">Proses retur (kurangi stok)</button></div>
</form>
</div></div></div>
@endsection
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Item diterima <span class="ms-2"><x-badge :status="$gr->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Diterima</th><th class="text-end">Reject</th><th>Batch</th><th>Expired</th></tr></thead>
<tbody>
@foreach($gr->items as $it)
<tr><td>{{ $it->ingredient->name ?? '-' }}</td><td class="text-end">{{ number_format($it->qty_received, 2) }}</td><td class="text-end">{{ number_format($it->qty_rejected, 2) }}</td><td>{{ $it->batch_no ?? '-' }}</td><td class="text-secondary">{{ $it->expiry_date ?? '-' }}</td></tr>
@endforeach
</tbody></table></div></div>
@if($gr->supplierReturns->isNotEmpty())
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Retur ke supplier</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Bahan</th><th class="text-end">Qty</th><th>Alasan</th></tr></thead>
<tbody>
@foreach($gr->supplierReturns as $sr)
<tr><td class="text-secondary">{{ $sr->number }}</td><td>{{ $sr->ingredient->name ?? '' }}</td><td class="text-end">{{ number_format($sr->qty, 2) }}</td><td class="text-secondary">{{ $sr->reason }}</td></tr>
@endforeach
</tbody></table></div></div>
@endif
@endsection

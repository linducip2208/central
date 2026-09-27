@extends('layouts.app')
@section('title', 'PR ' . $pr->number)
@section('subtitle', 'Dapur: ' . ($pr->centralKitchen->name ?? '-') . ' · Diminta: ' . ($pr->requester->name ?? '-'))
@section('actions')
@if($pr->status === 'DRAFT')
<form method="POST" action="{{ route('purchase-requests.submit', $pr) }}" class="d-inline">@csrf<button class="btn btn-primary" type="submit">Submit</button></form>
<form method="POST" action="{{ route('purchase-requests.destroy', $pr) }}" class="d-inline" onsubmit="return confirm('Hapus PR?')">@csrf @method('DELETE')<button class="btn btn-ghost-danger" type="submit">Hapus</button></form>
@endif
<a href="{{ route('purchase-orders.create') }}" class="btn btn-white">Buatkan PO</a>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Item <span class="ms-2"><x-badge :status="$pr->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Diminta</th><th class="text-end">Disetujui</th><th class="text-end">Dipesan</th><th class="text-end">Est. harga</th></tr></thead>
<tbody>
@foreach($pr->items as $it)
<tr><td>{{ $it->ingredient->name ?? '-' }} <span class="text-secondary">({{ $it->ingredient->unit->symbol ?? '' }})</span></td>
<td class="text-end">{{ number_format($it->qty_requested, 2) }}</td>
<td class="text-end">{{ number_format($it->qty_approved, 2) }}</td>
<td class="text-end">{{ number_format($it->qty_ordered, 2) }}</td>
<td class="text-end">{{ mbg_currency($it->estimated_price) }}</td></tr>
@endforeach
</tbody></table></div></div>
</div>
<div class="col-lg-4">
@if($pr->status === 'SUBMITTED')
<div class="card"><div class="card-header"><h3 class="card-title">Persetujuan</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('purchase-requests.approve', $pr) }}">@csrf
@foreach($pr->items as $it)
<div class="mb-2"><label class="form-label">{{ $it->ingredient->name }} (minta {{ number_format($it->qty_requested, 2) }})</label>
<input name="approved[{{ $it->id }}]" type="number" step="0.001" min="0" value="{{ $it->qty_requested }}" class="form-control"/></div>
@endforeach
<button class="btn btn-success w-100" type="submit">Setujui</button>
</form>
<form method="POST" action="{{ route('purchase-requests.reject', $pr) }}" class="mt-2">@csrf
<div class="input-group"><input name="reject_reason" class="form-control" placeholder="Alasan penolakan" required/><button class="btn btn-danger" type="submit">Tolak</button></div>
</form>
</div></div>
@else
<div class="card"><div class="card-body">
<dl class="row small mb-0">
<dt class="col-5">Status</dt><dd class="col-7"><x-badge :status="$pr->status"/></dd>
<dt class="col-5">Disetujui oleh</dt><dd class="col-7">{{ $pr->approver->name ?? '-' }}</dd>
<dt class="col-5">Alasan tolak</dt><dd class="col-7">{{ $pr->reject_reason ?? '-' }}</dd>
<dt class="col-5">Catatan</dt><dd class="col-7">{{ $pr->notes ?? '-' }}</dd>
</dl>
</div></div>
@endif
</div>
</div>
@endsection

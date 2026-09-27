@extends('layouts.app')
@section('title', $supplier->name)
@section('subtitle', $supplier->code . ' · ' . $supplier->category)
@section('actions')
<a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-white">Ubah</a>
<a href="{{ route('suppliers.index') }}" class="btn btn-ghost-secondary">Kembali</a>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<div class="mb-2"><x-badge :status="$supplier->status"/></div>
<dl class="row">
<dt class="col-5">Kontak</dt><dd class="col-7">{{ $supplier->contact_person ?? '-' }}</dd>
<dt class="col-5">Telepon</dt><dd class="col-7">{{ $supplier->phone ?? '-' }}</dd>
<dt class="col-5">Email</dt><dd class="col-7">{{ $supplier->email ?? '-' }}</dd>
<dt class="col-5">NPWP</dt><dd class="col-7">{{ $supplier->tax_number ?? '-' }}</dd>
<dt class="col-5">Rating</dt><dd class="col-7">{{ $supplier->rating }}/5</dd>
<dt class="col-5">Alamat</dt><dd class="col-7">{{ $supplier->address ?? '-' }}</dd>
</dl>
<form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Hapus supplier ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Purchase order terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th class="text-end">Total</th><th>Status</th></tr></thead>
<tbody>
@forelse($supplier->purchaseOrders as $po)
<tr><td><a href="{{ route('purchase-orders.show', $po) }}">{{ $po->number }}</a></td><td class="text-secondary">{{ $po->order_date }}</td><td class="text-end">{{ mbg_currency($po->grand_total) }}</td><td><x-badge :status="$po->status"/></td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Belum ada PO.</td></tr>@endforelse
</tbody></table></div>
</div>
</div>
</div>
@endsection

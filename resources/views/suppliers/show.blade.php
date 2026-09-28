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
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Kontak</h3></div>
<div class="list-group list-group-flush">
@forelse($supplier->contacts as $c)
<div class="list-group-item"><div class="fw-bold">{{ $c->name }} @if($c->is_primary)<span class="badge bg-green-lt">UTAMA</span>@endif</div><div class="text-secondary small">{{ $c->position ?? '' }} · {{ $c->phone ?? '' }} · {{ $c->email ?? '' }}</div></div>
@empty<div class="list-group-item text-secondary">Belum ada kontak.</div>@endforelse
</div>
<div class="card-body border-top">
<form method="POST" action="{{ route('suppliers.contacts.store', $supplier) }}">@csrf
<div class="row g-1">
<div class="col-6"><input name="name" class="form-control form-control-sm" placeholder="Nama *" required/></div>
<div class="col-6"><input name="position" class="form-control form-control-sm" placeholder="Jabatan"/></div>
<div class="col-6"><input name="phone" class="form-control form-control-sm" placeholder="Telepon"/></div>
<div class="col-6"><div class="input-group"><input name="email" type="email" class="form-control form-control-sm" placeholder="Email"/><button class="btn btn-sm btn-white" type="submit">+</button></div></div>
</div>
</form>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Alamat</h3></div>
<div class="list-group list-group-flush">
@forelse($supplier->addresses as $ad)
<div class="list-group-item"><div class="fw-bold">{{ $ad->label }} @if($ad->is_default)<span class="badge bg-green-lt">DEFAULT</span>@endif</div><div class="text-secondary small">{{ $ad->address }} {{ $ad->city }}</div></div>
@empty<div class="list-group-item text-secondary">Belum ada alamat.</div>@endforelse
</div>
<div class="card-body border-top">
<form method="POST" action="{{ route('suppliers.addresses.store', $supplier) }}">@csrf
<div class="row g-1">
<div class="col-4"><input name="label" class="form-control form-control-sm" value="WAREHOUSE" required/></div>
<div class="col-8"><input name="address" class="form-control form-control-sm" placeholder="Alamat *" required/></div>
<div class="col-8"><input name="city" class="form-control form-control-sm" placeholder="Kota"/></div>
<div class="col-4"><button class="btn btn-sm btn-white w-100" type="submit">Tambah</button></div>
</div>
</form>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Price list (dipakai MRP)</h3></div>
<div class="table-responsive"><table class="table table-sm table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Harga</th><th class="text-end">MOQ</th><th class="text-end">Lead</th></tr></thead>
<tbody>
@forelse($supplier->priceLists as $pl)
<tr><td>{{ $pl->ingredient->name ?? '' }}</td><td class="text-end">{{ mbg_currency($pl->price) }}</td><td class="text-end">{{ number_format($pl->moq, 2) }}</td><td class="text-end">{{ $pl->lead_time_days }} hr</td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-2">Belum ada price list.</td></tr>@endforelse
</tbody></table></div>
<div class="card-body border-top">
<form method="POST" action="{{ route('suppliers.prices.store', $supplier) }}">@csrf
<div class="row g-1">
<div class="col-5"><select name="ingredient_id" class="form-select form-select-sm" required><option value="">— bahan —</option>@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach</select></div>
<div class="col-3"><input name="price" type="number" step="0.01" min="0" class="form-control form-control-sm" placeholder="harga *" required/></div>
<div class="col-2"><input name="moq" type="number" step="0.001" min="0" class="form-control form-control-sm" placeholder="MOQ"/></div>
<div class="col-2"><button class="btn btn-sm btn-white w-100" type="submit">OK</button></div>
</div>
</form>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Kontrak</h3></div>
<div class="list-group list-group-flush">
@forelse($supplier->contracts as $ct)
<div class="list-group-item d-flex justify-content-between"><div><div class="fw-bold">{{ $ct->number }}</div><div class="text-secondary small">{{ $ct->start_date }} – {{ $ct->end_date }} · {{ $ct->payment_terms }} @if($ct->isValid())· <span class="badge bg-green-lt">BERLAKU</span>@endif</div></div><x-badge :status="$ct->status"/></div>
@empty<div class="list-group-item text-secondary">Belum ada kontrak.</div>@endforelse
</div>
<div class="card-body border-top">
<form method="POST" action="{{ route('suppliers.contracts.store', $supplier) }}">@csrf
<div class="row g-1">
<div class="col-4"><input name="start_date" type="date" class="form-control form-control-sm" required/></div>
<div class="col-4"><input name="end_date" type="date" class="form-control form-control-sm" required/></div>
<div class="col-4"><select name="payment_terms" class="form-select form-select-sm"><option>CREDIT</option><option>CASH</option><option>COD</option></select></div>
<div class="col-12 mt-1"><div class="input-group"><input name="notes" class="form-control form-control-sm" placeholder="Catatan"/><button class="btn btn-sm btn-white" type="submit">Tambah kontrak</button></div></div>
</div>
</form>
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

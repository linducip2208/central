@extends('layouts.app')
@section('title', 'Delivery ' . $delivery->number)
@section('subtitle', ($delivery->school->name ?? '-') . ' · rencana ' . number_format($delivery->qty_planned) . ' porsi')
@section('actions')
<a href="{{ route('returns.create', $delivery) }}" class="btn btn-white">Terima retur</a>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Item <span class="ms-2"><x-badge :status="$delivery->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Produk</th><th class="text-end">Rencana</th><th class="text-end">Terkirim</th><th class="text-end">Return</th></tr></thead>
<tbody>
@foreach($delivery->items as $it)
<tr><td>{{ $it->product->name ?? '-' }}</td><td class="text-end">{{ number_format($it->qty_planned) }}</td><td class="text-end">{{ number_format($it->qty_delivered) }}</td><td class="text-end">{{ number_format($it->qty_returned) }}</td></tr>
@endforeach
</tbody></table></div></div>

@if(in_array($delivery->status, ['PLANNED','IN_TRANSIT']))
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Serah terima (kurangi stok FEFO)</h3></div>
<div class="card-body">
@if($delivery->delivery_proof)
<div class="card mb-3"><div class="card-header"><h3 class="card-title">Bukti serah terima</h3></div>
<div class="card-body"><a href="{{ Storage::url($delivery->delivery_proof) }}" target="_blank"><img src="{{ Storage::url($delivery->delivery_proof) }}" alt="Bukti" style="max-height:220px" class="rounded border"/></a></div></div>
@endif
<form method="POST" action="{{ route('deliveries.deliver', $delivery) }}" enctype="multipart/form-data">@csrf
<div class="row g-2">
<div class="col-md-4"><label class="form-label">Gudang asal *</label>
<select name="warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Diterima *</label><input name="qty_delivered" type="number" min="0" max="{{ $delivery->qty_planned }}" value="{{ $delivery->qty_planned }}" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Return</label><input name="qty_returned" type="number" min="0" value="0" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Diterima oleh *</label><input name="received_by_name" class="form-control" required placeholder="Nama penerima"/></div>
<div class="col-md-4"><label class="form-label">Suhu (°C)</label><input name="temperature_c" type="number" step="0.1" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Foto bukti (jpg/png, maks 4MB)</label><input name="proof" type="file" accept="image/*" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<button class="btn btn-success mt-2" type="submit">Simpan serah terima</button>
</form>
<form method="POST" action="{{ route('deliveries.fail', $delivery) }}" class="mt-2">@csrf
<div class="input-group"><input name="notes" class="form-control" placeholder="Alasan gagal" required/><button class="btn btn-danger" type="submit">Tandai gagal</button></div>
</form>
</div></div>
@endif
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Tracking</h3></div>
<div class="list-group list-group-flush">
@forelse($delivery->trackings->sortByDesc('created_at') as $t)
<div class="list-group-item">
<div class="d-flex justify-content-between"><span class="badge bg-blue-lt">{{ $t->status }}</span><span class="text-secondary small">{{ $t->created_at->format('d M H:i') }}</span></div>
<div class="small mt-1">{{ $t->notes ?? '-' }}</div>
</div>
@empty<div class="list-group-item text-secondary">Belum ada tracking.</div>@endforelse
</div>
<div class="card-body border-top">
<form method="POST" action="{{ route('deliveries.track', $delivery) }}">@csrf
<div class="row g-2">
<div class="col-5"><input name="status" class="form-control" placeholder="Status *" required/></div>
<div class="col-7"><div class="input-group"><input name="notes" class="form-control" placeholder="Catatan"/><button class="btn btn-white" type="submit">Tambah</button></div></div>
</div>
</form>
</div></div>
</div>
</div>
@endsection

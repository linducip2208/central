@extends('layouts.app')
@section('title', 'Lokasi Gudang (WMS)')
@section('subtitle', 'Gudang → Zona → Rak → Bin · batch di-put-away ke bin')
@section('content')
@foreach($warehouses as $w)
<div class="card mb-3"><div class="card-header"><h3 class="card-title">{{ $w->name }} <span class="text-secondary">({{ $w->code }})</span></h3>
<div class="ms-auto d-flex gap-2">
<form method="POST" action="{{ route('wms.zones.store') }}" class="d-flex gap-1">@csrf<input type="hidden" name="warehouse_id" value="{{ $w->id }}"/><input name="code" class="form-control form-control-sm" style="width:80px" placeholder="kode" required/><input name="name" class="form-control form-control-sm" placeholder="nama zona" required/><select name="zone_type" class="form-select form-select-sm"><option>STORAGE</option><option>RECEIVING</option><option>QUARANTINE</option><option>DISPATCH</option></select><button class="btn btn-sm btn-white" type="submit">+ Zona</button></form>
</div></div>
<div class="card-body">
@foreach($w->zones as $z)
<div class="mb-3 border rounded p-2">
<div class="d-flex justify-content-between align-items-center mb-2">
<strong>{{ $z->code }} — {{ $z->name }}</strong><span class="badge bg-blue-lt">{{ $z->zone_type }}</span>
</div>
<div class="row g-2">
@foreach($z->racks as $r)
<div class="col-md-4">
<div class="border rounded p-2">
<div class="fw-bold small mb-1">Rak {{ $r->code }}</div>
@foreach($r->bins as $b)
<div class="d-flex justify-content-between small py-1 border-top">
<span><code>{{ $b->barcode }}</code> @if(!$b->is_active)<span class="badge bg-secondary-lt">off</span>@endif <span class="text-secondary">({{ $b->batches_count ?? '' }})</span></span>
</div>
@endforeach
<form method="POST" action="{{ route('wms.bins.store') }}" class="d-flex gap-1 mt-1">@csrf<input type="hidden" name="warehouse_rack_id" value="{{ $r->id }}"/><input name="code" class="form-control form-control-sm" placeholder="kode bin" required/><button class="btn btn-sm btn-white" type="submit">+ Bin</button></form>
</div>
</div>
@endforeach
<div class="col-md-4">
<form method="POST" action="{{ route('wms.racks.store') }}" class="border rounded p-2">@csrf<input type="hidden" name="warehouse_zone_id" value="{{ $z->id }}"/>
<div class="fw-bold small mb-1">+ Rak baru</div>
<div class="d-flex gap-1"><input name="code" class="form-control form-control-sm" placeholder="kode" required/><input name="name" class="form-control form-control-sm" placeholder="nama" required/><button class="btn btn-sm btn-white" type="submit">+</button></div>
</form>
</div>
</div>
</div>
@endforeach
</div></div>
@endforeach
@endsection

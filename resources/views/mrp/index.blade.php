@extends('layouts.app')
@section('title', 'MRP')
@section('content')
<div class="card mb-3"><div class="card-header"><h3 class="card-title">Jalankan MRP</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('mrp.run') }}">@csrf
<div class="row g-2">
<div class="col-md-5"><label class="form-label">Demand plan (APPROVED) *</label>
<select name="demand_plan_id" class="form-select" required>@foreach($plans as $p)<option value="{{ $p->id }}">{{ $p->number }} · {{ $p->period_start }}–{{ $p->period_end }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit">Hitung kebutuhan</button></div>
</div>
<p class="text-secondary small mt-2 mb-0">Gross (explosion BOM/resep) − tersedia − incoming PO + safety → net + rekomendasi supplier termurah.</p>
</form>
</div></div>
<div class="card"><div class="card-header"><h3 class="card-title">Riwayat MRP run</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Gudang</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($runs as $r)
<tr><td><a href="{{ route('mrp.show', $r) }}">{{ $r->number }}</a></td><td class="text-secondary">{{ $r->run_date }}</td><td class="text-secondary">{{ $r->warehouse->name ?? '' }}</td><td><x-badge :status="$r->status"/></td><td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('mrp.show', $r) }}">Buka</a></td></tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada MRP run"/></td></tr>
@endforelse
</tbody></table></div>
<div class="card-body">{{ $runs->links() }}</div>
</div>
@endsection

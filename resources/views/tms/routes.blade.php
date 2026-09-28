@extends('layouts.app')
@section('title', 'Rute Delivery (TMS)')
@section('actions')<a href="{{ route('tms.tower') }}" class="btn btn-primary">Control Tower</a>@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Kendaraan</th><th>Kurir</th><th class="text-end">Stop</th><th></th></tr></thead>
<tbody>
@forelse($routes as $r)
<tr>
<td class="text-secondary">{{ $r->code }}</td>
<td><a href="{{ route('tms.routes.show', $r) }}">{{ $r->name }}</a></td>
<td class="text-secondary">{{ $r->vehicle->plate_no ?? '-' }}</td>
<td class="text-secondary">{{ $r->driver->name ?? '-' }}</td>
<td class="text-end">{{ $r->stops->count() }}</td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('tms.routes.show', $r) }}">Kelola</a></td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada rute"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $routes->links() }}</div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Rute baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('tms.routes.store') }}">@csrf
<div class="mb-2"><label class="form-label">Dapur *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach(\App\Models\CentralKitchen::active()->get() as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Kendaraan</label>
<select name="vehicle_id" class="form-select"><option value="">—</option>@foreach(\App\Models\Vehicle::active()->get() as $v)<option value="{{ $v->id }}">{{ $v->plate_no }} — {{ $v->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Kurir</label>
<select name="driver_id" class="form-select"><option value="">—</option>@foreach(\App\Models\User::role('driver')->get() as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
<button class="btn btn-primary" type="submit">Buat rute</button>
</form>
</div></div>
</div>
</div>
@endsection

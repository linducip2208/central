@extends('layouts.app')
@section('title', 'Rute ' . $route->name)
@section('subtitle', ($route->vehicle->plate_no ?? 'tanpa kendaraan') . ' · ' . ($route->driver->name ?? 'tanpa kurir'))
@section('actions')
<form method="POST" action="{{ route('tms.routes.optimize', $route) }}" class="d-inline">@csrf<button class="btn btn-white" type="submit" title="Butuh koordinat dapur + sekolah">Optimasi urutan</button></form>
<form method="POST" action="{{ route('tms.routes.apply', $route) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Terapkan ke delivery aktif</button></form>
@endsection
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Stop berurutan</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('tms.stops.store', $route) }}">@csrf
<div class="row g-2 mb-3">
<div class="col-md-5"><select name="school_id" class="form-select" required><option value="">— sekolah —</option>@foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
<div class="col-md-2"><input name="sequence" type="number" min="0" value="{{ $route->stops->count() + 1 }}" class="form-control" title="urutan"/></div>
<div class="col-md-2"><input name="window_start" type="time" class="form-control" title="window mulai"/></div>
<div class="col-md-2"><input name="window_end" type="time" class="form-control" title="window selesai"/></div>
<div class="col-md-1"><button class="btn btn-white w-100" type="submit">+</button></div>
</div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th class="text-end">#</th><th>Sekolah</th><th>Window</th><th></th></tr></thead>
<tbody>
@foreach($route->stops as $s)
<tr><td class="text-end fw-bold">{{ $s->sequence }}</td><td>{{ $s->school->name ?? '' }}</td><td class="text-secondary">{{ $s->window_start ?? '-' }} – {{ $s->window_end ?? '-' }}</td>
<td class="text-end"><form method="POST" action="{{ route('tms.stops.destroy', $s) }}" onsubmit="return confirm('Hapus stop?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form></td></tr>
@endforeach
</tbody></table></div>
</div></div>
@endsection

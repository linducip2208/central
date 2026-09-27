@extends('layouts.app')
@section('title', 'Demand Planning')
@section('actions')<a href="{{ route('demands.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Demand</a>@endsection
@section('content')
<div class="card mb-3"><div class="card-header"><h3 class="card-title">Agregasi demand → draft Purchase Request</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('demands.generate-pr') }}">@csrf
<div class="row g-2">
<div class="col-md-3"><label class="form-label">Dapur</label><select name="central_kitchen_id" class="form-select">@foreach(\App\Models\CentralKitchen::active()->get() as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Dari</label><input name="from" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-md-2"><label class="form-label">Sampai</label><input name="to" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-md-3"><label class="form-label">Gudang tujuan</label><select name="warehouse_id" class="form-select">@foreach(\App\Models\Warehouse::all() as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-2 d-flex align-items-end"><button class="btn btn-success w-100" type="submit">Generate PR</button></div>
</div>
<p class="text-secondary small mt-2 mb-0">Kebutuhan bahan dihitung otomatis dari resep (explosion) dikali porsi.</p>
</form>
</div></div>
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','PLANNED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Tanggal</th><th>Sekolah</th><th>Menu</th><th class="text-end">Porsi</th><th>Sumber</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($demands as $d)
<tr>
<td class="text-secondary">{{ $d->code }}</td>
<td class="text-secondary">{{ $d->demand_date }}</td>
<td>{{ $d->school->name ?? 'Semua' }}</td>
<td class="text-secondary">{{ $d->menu->name ?? '-' }}</td>
<td class="text-end">{{ number_format($d->portions) }}</td>
<td>{{ $d->source }}</td>
<td><x-badge :status="$d->status"/></td>
<td class="text-end">@if($d->status === 'DRAFT')<form method="POST" action="{{ route('demands.destroy', $d) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form>@endif</td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada demand"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $demands->links() }}</div>
</div></div>
@endsection

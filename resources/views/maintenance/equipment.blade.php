@extends('layouts.app')
@section('title', 'Peralatan & Maintenance')
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Work center</th><th>Next maintenance</th><th>Status</th></tr></thead>
<tbody>
@forelse($items as $e)
<tr><td class="fw-bold">{{ $e->code }}</td><td>{{ $e->name }}</td><td class="text-secondary">{{ $e->workCenter->name ?? '—' }}</td>
<td>@if($e->next_maintenance_at){{ $e->next_maintenance_at }} @if($e->isOverdue())<span class="badge bg-red-lt">OVERDUE</span>@endif @else — @endif</td>
<td><x-badge :status="$e->status"/></td></tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada peralatan"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Catat maintenance</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('maintenance.logs', ['equipment' => 0]) }}" id="log-form" onsubmit="return setLogAction()">@csrf
<div class="row g-2">
<div class="col-md-4"><select id="log-eq" class="form-select">@foreach($items as $e)<option value="{{ $e->id }}">{{ $e->code }} — {{ $e->name }}</option>@endforeach</select></div>
<div class="col-md-2"><select name="maintenance_type" class="form-select"><option>PREVENTIVE</option><option>CORRECTIVE</option></select></div>
<div class="col-md-2"><input name="performed_at" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-md-2"><input name="cost" type="number" min="0" class="form-control" placeholder="biaya"/></div>
<div class="col-md-2"><button class="btn btn-white w-100" type="submit">Catat</button></div>
<div class="col-md-12"><input name="description" class="form-control" placeholder="Deskripsi pekerjaan *" required/></div>
</div>
</form>
</div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Peralatan baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('maintenance.equipment.store') }}">@csrf
<div class="mb-2"><label class="form-label">Dapur *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Kode *</label><input name="code" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Work center</label>
<select name="work_center_id" class="form-select"><option value="">—</option>@foreach(\App\Models\WorkCenter::all() as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Maintenance berikut</label><input name="next_maintenance_at" type="date" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection
@push('scripts')
<script>
function setLogAction() {
const id = document.getElementById('log-eq').value;
document.getElementById('log-form').action = `/maintenance/equipment/${id}/logs`;
return true;
}
</script>
@endpush

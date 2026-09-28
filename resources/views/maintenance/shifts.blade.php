@extends('layouts.app')
@section('title', 'Shift Kerja')
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Dapur</th><th>Jam</th><th>Status</th></tr></thead>
<tbody>
@forelse($shifts as $s)
<tr><td class="fw-bold">{{ $s->code }}</td><td>{{ $s->name }}</td><td class="text-secondary">{{ $s->centralKitchen->name ?? '' }}</td><td class="text-secondary">{{ substr($s->start_time, 0, 5) }}–{{ substr($s->end_time, 0, 5) }}</td><td>@if($s->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">OFF</span>@endif</td></tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada shift"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Shift baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('shifts.store') }}">@csrf
<div class="mb-2"><label class="form-label">Dapur *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="row g-2">
<div class="col-4"><label class="form-label">Kode *</label><input name="code" class="form-control" required placeholder="PAGI"/></div>
<div class="col-8"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="col-6"><label class="form-label">Mulai *</label><input name="start_time" type="time" class="form-control" required/></div>
<div class="col-6"><label class="form-label">Selesai *</label><input name="end_time" type="time" class="form-control" required/></div>
</div>
<button class="btn btn-primary mt-2" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection

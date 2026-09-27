@extends('layouts.app')
@section('title', 'Buat Demand')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('demands.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Sekolah (opsional)</label>
<select name="school_id" class="form-select"><option value="">— Semua/agregat —</option>@foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ number_format($s->target_portions) }} porsi)</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Menu (opsional)</label>
<select name="menu_id" class="form-select"><option value="">—</option>@foreach($menus as $m)<option value="{{ $m->id }}">{{ $m->name }} · {{ $m->menu_date }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Tanggal kebutuhan *</label><input name="demand_date" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-md-3"><label class="form-label">Jumlah porsi *</label><input name="portions" type="number" min="1" class="form-control" required/></div>
<div class="col-md-3"><label class="form-label">Sumber *</label>
<select name="source" class="form-select">@foreach(['SCHOOL','FORECAST','MANUAL'] as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan</button><a href="{{ route('demands.index') }}" class="btn btn-white">Batal</a></div>
</form>
</div></div>
@endsection

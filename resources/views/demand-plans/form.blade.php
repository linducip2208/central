@extends('layouts.app')
@section('title', 'Buat Demand Plan')
@section('subtitle', 'Gross = target porsi sekolah × hari · disesuaikan kehadiran + safety stock')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('demand-plans.store') }}">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">Dapur *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Tipe *</label>
<select name="period_type" class="form-select"><option value="WEEKLY">WEEKLY</option><option value="DAILY">DAILY</option><option value="MONTHLY">MONTHLY</option></select></div>
<div class="col-md-2"><label class="form-label">Dari *</label><input name="period_start" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<div class="col-md-2"><label class="form-label">Sampai *</label><input name="period_end" type="date" class="form-control" value="{{ now()->addDays(6)->toDateString() }}" required/></div>
<div class="col-md-3"><label class="form-label">Menu</label>
<select name="menu_id" class="form-select"><option value="">— agregat —</option>@foreach($menus as $m)<option value="{{ $m->id }}">{{ $m->name }} · {{ $m->menu_date }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Kehadiran (%)</label><input name="attendance_pct" type="number" min="0" max="100" value="95" class="form-control"/></div>
<div class="col-md-3"><label class="form-label">Safety (%)</label><input name="safety_pct" type="number" min="0" max="100" value="5" class="form-control"/></div>
<div class="col-md-6"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Sekolah *</label>
<div class="row g-1">
@foreach($schools as $s)
<div class="col-md-4"><label class="form-check"><input type="checkbox" name="school_ids[]" value="{{ $s->id }}" class="form-check-input" checked/><span class="form-check-label">{{ $s->name }} ({{ number_format($s->target_portions) }})</span></label></div>
@endforeach
</div></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Generate plan</button></div>
</form>
</div></div>
@endsection

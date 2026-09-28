@extends('layouts.app')
@section('title', 'Siklus ' . $cycle->name)
@section('actions')
@if($cycle->status === 'DRAFT')
<form method="POST" action="{{ route('menu-cycles.approve', $cycle) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Setujui siklus</button></form>
@endif
@endsection
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Penetapan menu per hari <span class="ms-2"><x-badge :status="$cycle->status"/></span></h3></div>
<div class="card-body">
<form method="POST" action="{{ route('menu-cycles.days', $cycle) }}">@csrf
<div class="row g-2">
<div class="col-md-3"><select name="day_no" class="form-select">@for($d = 1; $d <= $cycle->cycle_days; $d++)<option value="{{ $d }}">Hari {{ $d }} ({{ \Carbon\Carbon::parse($cycle->start_date)->addDays($d - 1)->format('d M') }})</option>@endfor</select></div>
<div class="col-md-7"><select name="menu_id" class="form-select">@foreach($menus as $m)<option value="{{ $m->id }}">{{ $m->name }} · {{ $m->menu_date }}</option>@endforeach</select></div>
<div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Tetapkan</button></div>
</div>
</form>
</div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Hari</th><th>Tanggal</th><th>Menu</th><th>Tipe</th></tr></thead>
<tbody>
@for($d = 1; $d <= $cycle->cycle_days; $d++)
@php $day = $cycle->days->firstWhere('day_no', $d); @endphp
<tr><td class="fw-bold">H{{ $d }}</td><td class="text-secondary">{{ \Carbon\Carbon::parse($cycle->start_date)->addDays($d - 1)->format('d M Y') }}</td><td>{{ $day?->menu->name ?? '— belum ditetapkan —' }}</td><td class="text-secondary">{{ $day?->menu->meal_type ?? '' }}</td></tr>
@endfor
</tbody></table></div></div>
@endsection

@extends('layouts.app')
@section('title', 'Plan ' . $plan->number)
@section('subtitle', $plan->period_type . ' · ' . $plan->period_start . ' s.d. ' . $plan->period_end)
@section('actions')
@if($plan->status === 'DRAFT')
<form method="POST" action="{{ route('demand-plans.approve', $plan) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Setujui</button></form>
@endif
@if($plan->status === 'APPROVED')
<a href="{{ route('mrp.index') }}" class="btn btn-primary">Jalankan MRP</a>
@endif
@endsection
@section('content')
<div class="row row-deck row-cards mb-3">
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Gross demand</div><div class="h1">{{ number_format($summary['gross']) }}</div></div></div></div>
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Adjusted (kehadiran)</div><div class="h1">{{ number_format($summary['adjusted']) }}</div></div></div></div>
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Net (+safety)</div><div class="h1">{{ number_format($summary['net']) }}</div></div></div></div>
</div>
<div class="card"><div class="card-header"><h3 class="card-title">Baris demand <span class="ms-2"><x-badge :status="$plan->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Sekolah</th><th>Menu</th><th class="text-end">Gross</th><th class="text-end">Absen</th><th class="text-end">Adj</th><th class="text-end">Safety</th><th class="text-end">Net</th>@if($plan->status === 'DRAFT')<th></th>@endif</tr></thead>
<tbody>
@foreach($lines as $l)
<tr><td class="text-secondary">{{ $l->demand_date }}</td><td>{{ $l->school->name ?? '-' }}</td><td class="text-secondary">{{ $l->menu->name ?? '-' }}</td><td class="text-end">{{ number_format($l->gross_demand) }}</td><td class="text-end">{{ number_format($l->attendance_adjustment) }}</td><td class="text-end">{{ number_format($l->adjusted_demand) }}</td><td class="text-end">{{ number_format($l->safety_stock) }}</td><td class="text-end fw-bold">{{ number_format($l->net_demand) }}</td>
@if($plan->status === 'DRAFT')
<td class="text-end"><form method="POST" action="{{ route('demand-plans.lines.update', [$plan, $l]) }}" class="d-flex gap-1 justify-content-end">@csrf @method('PUT')
<input name="manual_adjustment" type="number" class="form-control form-control-sm" style="width:90px" value="{{ $l->manual_adjustment }}" title="penyesuaian manual"/>
<input name="safety_stock" type="number" min="0" class="form-control form-control-sm" style="width:80px" value="{{ $l->safety_stock }}" title="safety"/>
<button class="btn btn-sm btn-white" type="submit">OK</button></form></td>
@endif
</tr>
@endforeach
</tbody></table></div>
<div class="card-body">{{ $lines->links() }}</div>
</div>
@endsection

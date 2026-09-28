@extends('layouts.app')
@section('title', 'Budget Dapur')
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Periode</th><th>Dapur</th><th class="text-end">Budget</th><th class="text-end">Realisasi*</th><th class="text-end">Sisa</th></tr></thead>
<tbody>
@foreach($budgets as $b)
@php
$real = \App\Models\Costing::where('central_kitchen_id', $b->central_kitchen_id)->where('costing_date', 'like', $b->period.'%')->sum('total_cost');
@endphp
<tr><td>{{ $b->period }}</td><td class="text-secondary">{{ $b->centralKitchen->name ?? '' }}</td><td class="text-end">{{ mbg_currency($b->amount) }}</td><td class="text-end">{{ mbg_currency($real) }}</td><td class="text-end fw-bold @if($b->amount - $real < 0) text-red @else text-green @endif">{{ mbg_currency($b->amount - $real) }}</td></tr>
@endforeach
</tbody></table></div>
<p class="text-secondary small mt-2">* Realisasi = total costing produksi pada periode tersebut.</p>
</div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Set budget</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('budgets.store') }}">@csrf
<div class="mb-2"><label class="form-label">Dapur *</label>
<select name="central_kitchen_id" class="form-select" required>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Periode (YYYY-MM) *</label><input name="period" class="form-control" placeholder="2026-10" pattern="\d{4}-(0[1-9]|1[0-2])" required/></div>
<div class="mb-2"><label class="form-label">Nominal *</label><input name="amount" type="number" min="0" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Simpan</button>
</form>
</div></div>
</div>
</div>
@endsection

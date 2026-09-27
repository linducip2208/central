@extends('layouts.app')
@section('title', 'Laporan Kedaluwarsa')
@section('subtitle', 'Batch expired / kedaluwarsa dalam ' . $days . ' hari')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="days" class="form-select" onchange="this.form.submit()">@foreach([7,14,30,60,90] as $d)<option value="{{ $d }}" @selected($days == $d)>≤ {{ $d }} hari</option>@endforeach</select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Batch</th><th>Gudang</th><th>Expired</th><th class="text-end">Sisa</th><th>Sisa hari</th></tr></thead>
<tbody>
@forelse($batches as $b)
<tr><td class="fw-bold">{{ $b->batch_no }}</td><td class="text-secondary">{{ $b->warehouse->name ?? '' }}</td><td>{{ $b->expiry_date }}</td><td class="text-end">{{ number_format($b->remaining_qty, 2) }}</td><td>@if(\Carbon\Carbon::parse($b->expiry_date)->isPast())<span class="badge bg-red-lt">EXPIRED</span>@else<span class="badge bg-yellow-lt">{{ \Carbon\Carbon::parse($b->expiry_date)->diffInDays(now()) }} hari</span>@endif</td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-3">Tidak ada batch kritis.</td></tr>
@endforelse
</tbody></table></div>
</div></div>
@endsection

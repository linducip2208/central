@extends('layouts.app')
@section('title', 'Laporan Produksi')
@section('subtitle', $from . ' s.d. ' . $to)
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="from" type="date" class="form-control" value="{{ $from }}"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="{{ $to }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>WO</th><th>Produk</th><th class="text-end">Rencana</th><th class="text-end">Hasil</th><th class="text-end">Reject</th><th>Status</th></tr></thead>
<tbody>
@foreach($orders as $o)
<tr><td class="text-secondary">{{ $o->production_date }}</td><td>{{ $o->number }}</td><td class="text-secondary">{{ $o->product->name ?? '' }}</td><td class="text-end">{{ number_format($o->planned_qty, 0) }}</td><td class="text-end">{{ number_format($o->produced_qty, 0) }}</td><td class="text-end">{{ number_format($o->rejected_qty, 0) }}</td><td><x-badge :status="$o->status"/></td></tr>
@endforeach
<tr class="fw-bold"><td colspan="3" class="text-end">Total hasil</td><td class="text-end" colspan="4">{{ number_format($orders->sum('produced_qty'), 0) }} porsi</td></tr>
</tbody></table></div>
</div></div>
@endsection

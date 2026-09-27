@extends('layouts.app')
@section('title', 'Laporan Pengiriman')
@section('subtitle', $from . ' s.d. ' . $to . ' · fulfillment ' . $fulfillment . '%')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="from" type="date" class="form-control" value="{{ $from }}"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="{{ $to }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Nomor</th><th>Sekolah</th><th class="text-end">Rencana</th><th class="text-end">Terkirim</th><th class="text-end">Return</th><th>Status</th></tr></thead>
<tbody>
@foreach($deliveries as $d)
<tr><td class="text-secondary">{{ $d->delivery_date }}</td><td>{{ $d->number }}</td><td class="text-secondary">{{ $d->school->name ?? '' }}</td><td class="text-end">{{ number_format($d->qty_planned) }}</td><td class="text-end">{{ number_format($d->qty_delivered) }}</td><td class="text-end">{{ number_format($d->qty_returned) }}</td><td><x-badge :status="$d->status"/></td></tr>
@endforeach
</tbody></table></div>
</div></div>
@endsection

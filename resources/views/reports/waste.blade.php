@extends('layouts.app')
@section('title', 'Waste Analytics')
@section('subtitle', $from . ' s.d. ' . $to . ' · total rugi ' . mbg_currency($total))
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-3"><input name="from" type="date" class="form-control" value="{{ $from }}"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="{{ $to }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
</div></div>
<div class="row g-3">
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Rugi per alasan</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Alasan</th><th class="text-end">Kejadian</th><th class="text-end">Qty</th><th class="text-end">Rugi</th></tr></thead>
<tbody>
@foreach($rows as $r)
<tr><td>{{ $r->reason }}</td><td class="text-end">{{ $r->n }}</td><td class="text-end">{{ number_format($r->qty, 2) }}</td><td class="text-end text-red">{{ mbg_currency($r->loss) }}</td></tr>
@endforeach
</tbody></table></div></div>
</div>
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Kejadian terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Nomor</th><th>Item</th><th>Alasan</th><th class="text-end">Rugi</th></tr></thead>
<tbody>
@foreach($detail as $w)
<tr><td class="text-secondary">{{ $w->waste_date }}</td><td class="text-secondary">{{ $w->number }}</td><td class="text-secondary">{{ $w->item_type }} #{{ $w->item_id }}</td><td>{{ $w->reason }}</td><td class="text-end">{{ mbg_currency($w->cost_loss) }}</td></tr>
@endforeach
</tbody></table></div></div>
</div>
</div>
@endsection

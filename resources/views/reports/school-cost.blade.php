@extends('layouts.app')
@section('title', 'Alokasi Biaya per Sekolah')
@section('subtitle', $from . ' s.d. ' . $to . ' · tarif rata-rata ' . mbg_currency($avgCost) . '/porsi')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="from" type="date" class="form-control" value="{{ $from }}"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="{{ $to }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Sekolah</th><th class="text-end">Porsi terkirim</th><th class="text-end">Retur</th><th class="text-end">Alokasi biaya</th></tr></thead>
<tbody>
@foreach($rows as $r)
<tr><td>{{ $r['school'] }}</td><td class="text-end">{{ number_format($r['portions']) }}</td><td class="text-end">{{ number_format($r['returned']) }}</td><td class="text-end">{{ mbg_currency($r['cost']) }}</td></tr>
@endforeach
<tr class="fw-bold"><td class="text-end" colspan="3">Total</td><td class="text-end">{{ mbg_currency($rows->sum('cost')) }}</td></tr>
</tbody></table></div>
</div></div>
@endsection

@extends('layouts.app')
@section('title', 'Supplier Scorecard')
@section('subtitle', $from . ' s.d. ' . $to)
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="from" type="date" class="form-control" value="{{ $from }}"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="{{ $to }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Supplier</th><th class="text-end">PO</th><th class="text-end">Tepat waktu*</th><th class="text-end">Belanja</th><th class="text-end">QC gagal</th><th class="text-end">Rating</th></tr></thead>
<tbody>
@foreach($suppliers as $s)
<tr><td>{{ $s['name'] }}<div class="text-secondary small">{{ $s['code'] }}</div></td><td class="text-end">{{ $s['po_count'] }}</td><td class="text-end">{{ $s['on_time_pct'] !== null ? $s['on_time_pct'].'%' : '—' }}</td><td class="text-end">{{ mbg_currency($s['spend']) }}</td><td class="text-end @if($s['qc_failed'] > 0) text-red @endif">{{ $s['qc_failed'] }}</td><td class="text-end">{{ $s['rating'] }}/5</td></tr>
@endforeach
</tbody></table></div>
<p class="text-secondary small mt-2">* Tepat waktu dihitung dari expected_date vs order_date sebagai proksi (jadwal GR tercatat di goods receipt).</p>
</div></div>
@endsection

@extends('layouts.app')
@section('title', 'Capacity Planning')
@section('subtitle', 'Beban vs kapasitas (8 jam kerja/hari) · OVERLOAD >100% · TIGHT ≥85%')
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-4"><select name="central_kitchen_id" class="form-select" onchange="this.form.submit()">@foreach($kitchens as $k)<option value="{{ $k->id }}" @selected($kitchenId == $k->id)>{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-3"><input name="from" type="date" class="form-control" value="{{ $from }}"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="{{ $to }}"/></div>
<div class="col-md-2"><button class="btn btn-white w-100" type="submit">Hitung</button></div>
</form>
</div></div>
<div class="card"><div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th class="text-end">Rencana</th><th class="text-end">Kapasitas</th><th style="width:30%">Utilisasi</th><th>Flag</th></tr></thead>
<tbody>
@foreach($days as $d)
<tr>
<td class="text-secondary">{{ \Carbon\Carbon::parse($d['date'])->format('D, d M') }}</td>
<td class="text-end">{{ number_format($d['planned']) }}</td>
<td class="text-end">{{ number_format($d['capacity']) }}</td>
<td><div class="progress"><div class="progress-bar @if($d['flag'] === 'OVERLOAD') bg-red @elseif($d['flag'] === 'TIGHT') bg-yellow @else bg-green @endif" style="width: {{ min(100, $d['utilization']) }}%"></div></div><span class="small text-secondary">{{ $d['utilization'] }}%</span></td>
<td>@if($d['flag'] === 'OVERLOAD')<span class="badge bg-red-lt">OVERLOAD</span>@elseif($d['flag'] === 'TIGHT')<span class="badge bg-yellow-lt">TIGHT</span>@else<span class="badge bg-green-lt">OK</span>@endif</td>
</tr>
@endforeach
</tbody></table></div></div>
@endsection

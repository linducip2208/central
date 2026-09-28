@extends('layouts.app')
@section('title', 'Banding BOM + Simulasi')
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" action="{{ route('boms.compare') }}">
<div class="row g-2">
<div class="col-md-4"><select name="a_id" class="form-select">@foreach($boms as $b)<option value="{{ $b->id }}" @selected(request('a_id') == $b->id)>{{ $b->code }} (v{{ $b->version }})</option>@endforeach</select></div>
<div class="col-md-4"><select name="b_id" class="form-select">@foreach($boms as $b)<option value="{{ $b->id }}" @selected(request('b_id') == $b->id)>{{ $b->code }} (v{{ $b->version }})</option>@endforeach</select></div>
<div class="col-md-2"><input name="qty" type="number" step="0.01" min="0.01" value="{{ $qty ?? 100 }}" class="form-control" title="qty simulasi"/></div>
<div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Bandingkan</button></div>
</div>
</form>
</div></div>
@if(isset($a))
<div class="row g-3">
@foreach(['A' => [$a, $expA], 'B' => [$b, $expB]] as $label => [$bom, $exp])
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">{{ $label }}: {{ $bom->code }} (v{{ $bom->version }})</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Butuh ({{ number_format($qty, 0) }})</th><th class="text-end">Stok</th><th class="text-end">Kurang</th></tr></thead>
<tbody>
@forelse($simulate[$label] ?? [] as $row)
<tr><td>{{ $row['name'] }}</td><td class="text-end">{{ number_format($row['need'], 2) }}</td><td class="text-end">{{ number_format($row['stock'], 2) }}</td><td class="text-end @if($row['short'] > 0) text-red fw-bold @endif">{{ number_format($row['short'], 2) }}</td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-2">Tidak ada kebutuhan / explosion gagal.</td></tr>@endforelse
</tbody></table></div></div>
</div>
@endforeach
</div>
@endif
@endsection

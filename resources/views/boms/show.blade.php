@extends('layouts.app')
@section('title', 'BOM ' . $bom->code)
@section('subtitle', ($bom->version ? 'v'.$bom->version.' · ' : '') . 'yield ' . $bom->yield_qty . ' · berlaku ' . ($bom->effective_from ?? '-'))
@section('actions')
<form method="POST" action="{{ route('boms.clone', $bom) }}" class="d-inline">@csrf<button class="btn btn-white" type="submit">Clone revisi</button></form>
@if($bom->status === 'DRAFT')
<form method="POST" action="{{ route('boms.approve', $bom) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Aktifkan</button></form>
<form method="POST" action="{{ route('boms.destroy', $bom) }}" class="d-inline" onsubmit="return confirm('Hapus BOM?')">@csrf @method('DELETE')<button class="btn btn-ghost-danger" type="submit">Hapus</button></form>
@endif
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Komponen <span class="ms-2"><x-badge :status="$bom->status"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tipe</th><th>ID</th><th class="text-end">Qty</th><th class="text-end">Susut+Waste</th><th class="text-end">Efektif</th></tr></thead>
<tbody>
@foreach($bom->items as $it)
<tr><td><span class="badge bg-blue-lt">{{ $it->component_type }}</span></td><td class="text-secondary">#{{ $it->component_id }}</td><td class="text-end">{{ number_format($it->qty, 4) }}</td><td class="text-end">{{ $it->scrap_pct }}%+{{ $it->waste_pct }}%</td><td class="text-end fw-bold">{{ number_format($it->effectiveQty(), 4) }}</td></tr>
@endforeach
</tbody></table></div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Explosion (per yield)</h3></div>
<div class="card-body">
@if(isset($explosion['error']))
<div class="alert alert-danger">{{ $explosion['error'] }}</div>
@elseif(empty($explosion))
<p class="text-secondary">Aktifkan BOM untuk melihat explosion.</p>
@else
<div class="table-responsive"><table class="table table-vcenter">
<thead><tr><th>Bahan</th><th class="text-end">Butuh</th><th>Jalur</th></tr></thead>
<tbody>
@foreach($explosion as $row)
<tr><td>{{ $names[$row['ingredient_id']] ?? 'Bahan #'.$row['ingredient_id'] }}</td><td class="text-end">{{ number_format($row['qty'], 3) }}</td><td class="text-secondary small">{{ $row['path'] }}</td></tr>
@endforeach
</tbody></table></div>
@endif
</div></div>
</div>
</div>
@endsection

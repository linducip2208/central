@extends('layouts.app')
@section('title', 'Inspeksi ' . $inspection->number)
@section('content')
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Hasil ukur <span class="ms-2"><x-badge :status="$inspection->result"/></span></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Parameter</th><th class="text-end">Terukur</th><th>Status</th></tr></thead>
<tbody>
@foreach($inspection->results ?? [] as $r)
<tr><td>{{ $r['parameter'] }}</td><td class="text-end">{{ $r['measured'] ?? '—' }}</td><td>@if($r['pass'])<span class="badge bg-green-lt">LULUS</span>@else<span class="badge bg-red-lt">GAGAL</span>@endif</td></tr>
@endforeach
</tbody></table></div>
<div class="card-body">
@if($inspection->photo_path)<a href="{{ Storage::url($inspection->photo_path) }}" target="_blank"><img src="{{ Storage::url($inspection->photo_path) }}" style="max-height:180px" class="rounded border"/></a>@endif
<p class="text-secondary small mt-2 mb-0">Suhu: {{ $inspection->temperature_c ?? '—' }} · {{ $inspection->notes ?? '' }}</p>
</div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Buat NCR dari temuan</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('inspections.ncr', $inspection) }}">@csrf
<div class="row g-2">
<div class="col-md-6"><label class="form-label">Batch terkait</label>
<select name="batch_id" class="form-select"><option value="">—</option>@foreach(\App\Models\Batch::where('organization_id', $inspection->organization_id)->latest()->take(50)->get() as $b)<option value="{{ $b->id }}">{{ $b->batch_no }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Kategori *</label>
<select name="category" class="form-select">@foreach(['MATERIAL','PROCESS','HYGIENE','EQUIPMENT','FOREIGN_OBJECT','OTHER'] as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Severity *</label>
<select name="severity" class="form-select"><option>MINOR</option><option>MAJOR</option><option>CRITICAL</option></select></div>
<div class="col-md-6"><label class="form-label">Disposisi *</label>
<select name="disposition" class="form-select"><option>HOLD</option><option>REWORK</option><option>REJECT</option><option>RELEASE</option></select></div>
<div class="col-md-12"><label class="form-label">Deskripsi *</label><textarea name="description" class="form-control" rows="2" required></textarea></div>
</div>
<button class="btn btn-warning mt-2" type="submit">Buat NCR</button>
</form>
</div></div>
</div>
</div>
@endsection

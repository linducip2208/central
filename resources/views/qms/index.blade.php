@extends('layouts.app')
@section('title', 'Inspeksi Mutu (QMS)')
@section('actions')
<a href="{{ route('inspections.templates') }}" class="btn btn-white">Template</a>
<a href="{{ route('ncrs.index') }}" class="btn btn-white">NCR/CAPA</a>
<a href="{{ route('temp.index') }}" class="btn btn-white">Suhu CCP</a>
<a href="{{ route('inspections.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Inspeksi</a>
@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="result" class="form-select" onchange="this.form.submit()"><option value="">— Semua hasil —</option>@foreach(['PENDING','PASSED','FAILED'] as $r)<option value="{{ $r }}" @selected(request('result') === $r)>{{ $r }}</option>@endforeach</select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Template</th><th>Referensi</th><th class="text-end">Suhu</th><th>Hasil</th><th></th></tr></thead>
<tbody>
@forelse($inspections as $i)
<tr>
<td><a href="{{ route('inspections.show', $i) }}">{{ $i->number }}</a></td>
<td class="text-secondary">{{ $i->inspection_date }}</td>
<td class="text-secondary">{{ $i->template->name ?? '—' }}</td>
<td class="text-secondary">{{ class_basename($i->reference_type) }} #{{ $i->reference_id }}</td>
<td class="text-end">{{ $i->temperature_c ?? '—' }}</td>
<td><x-badge :status="$i->result"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('inspections.show', $i) }}">Buka</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada inspeksi"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $inspections->links() }}</div>
</div></div>
@endsection

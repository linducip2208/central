@extends('layouts.app')
@section('title', 'Quality Control')
@section('actions')<a href="{{ route('quality-controls.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Uji QC</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="result" class="form-select" onchange="this.form.submit()"><option value="">— Semua hasil —</option>@foreach(['PENDING','PASSED','FAILED','CONDITIONAL'] as $r)<option value="{{ $r }}" @selected(request('result') === $r)>{{ $r }}</option>@endforeach</select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Referensi</th><th>Tipe uji</th><th class="text-end">Sampel</th><th class="text-end">Lulus</th><th class="text-end">Gagal</th><th>Hasil</th></tr></thead>
<tbody>
@forelse($qcs as $qc)
<tr>
<td class="text-secondary">{{ $qc->number }}</td>
<td class="text-secondary">{{ $qc->check_date }}</td>
<td class="text-secondary">{{ class_basename($qc->reference_type) }} #{{ $qc->reference_id }}</td>
<td>{{ $qc->check_type }}</td>
<td class="text-end">{{ $qc->sample_qty }}</td>
<td class="text-end text-green">{{ $qc->pass_qty }}</td>
<td class="text-end text-red">{{ $qc->fail_qty }}</td>
<td><x-badge :status="$qc->result"/></td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada uji QC"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $qcs->links() }}</div>
</div></div>
@endsection

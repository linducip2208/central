@extends('layouts.app')
@section('title', 'Template Inspeksi')
@section('content')
<div class="row g-3">
<div class="col-lg-7">
@foreach($templates as $t)
<div class="card mb-3"><div class="card-header"><h3 class="card-title">{{ $t->code }} — {{ $t->name }} <span class="badge bg-blue-lt ms-1">{{ $t->stage }}</span></h3></div>
<div class="table-responsive"><table class="table table-sm table-vcenter card-table">
<thead><tr><th>Parameter</th><th class="text-end">Min</th><th class="text-end">Max</th><th>Satuan</th></tr></thead>
<tbody>
@foreach($t->parameters as $p)
<tr><td>{{ $p['name'] }}</td><td class="text-end">{{ $p['spec_min'] ?? '—' }}</td><td class="text-end">{{ $p['spec_max'] ?? '—' }}</td><td class="text-secondary">{{ $p['unit'] ?? '' }}</td></tr>
@endforeach
</tbody></table></div></div>
@endforeach
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Template baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('inspections.templates.store') }}">@csrf
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Tahap *</label>
<select name="stage" class="form-select"><option value="INCOMING">INCOMING</option><option value="IN_PROCESS">IN_PROCESS</option><option value="FINISHED">FINISHED</option></select></div>
<div id="params">
<div class="row g-1 mb-1"><div class="col-5"><input name="parameters[0][name]" class="form-control" placeholder="parameter *" required/></div><div class="col-2"><input name="parameters[0][spec_min]" type="number" step="0.01" class="form-control" placeholder="min"/></div><div class="col-2"><input name="parameters[0][spec_max]" type="number" step="0.01" class="form-control" placeholder="max"/></div><div class="col-3"><input name="parameters[0][unit]" class="form-control" placeholder="satuan"/></div></div>
</div>
<button type="button" class="btn btn-white btn-sm" onclick="addParam()">+ Parameter</button>
<button class="btn btn-primary btn-sm" type="submit">Simpan template</button>
</form>
</div></div>
</div>
</div>
@endsection
@push('scripts')
<script>
let pi2 = 1;
function addParam() {
document.getElementById('params').insertAdjacentHTML('beforeend', `<div class="row g-1 mb-1"><div class="col-5"><input name="parameters[${pi2}][name]" class="form-control" placeholder="parameter"/></div><div class="col-2"><input name="parameters[${pi2}][spec_min]" type="number" step="0.01" class="form-control" placeholder="min"/></div><div class="col-2"><input name="parameters[${pi2}][spec_max]" type="number" step="0.01" class="form-control" placeholder="max"/></div><div class="col-3"><input name="parameters[${pi2}][unit]" class="form-control" placeholder="satuan"/></div></div>`);
pi2++;
}
</script>
@endpush

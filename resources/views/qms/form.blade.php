@extends('layouts.app')
@section('title', 'Inspeksi Baru')
@section('subtitle', 'Nilai terukur di luar spec → FAILED otomatis + notifikasi + webhook')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('inspections.store') }}" enctype="multipart/form-data">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Template (opsional)</label>
<select name="inspection_template_id" id="tpl" class="form-select" onchange="fillParams()"><option value="">— tanpa template —</option>@foreach($templates as $t)<option value="{{ $t->id }}" data-params='{!! json_encode($t->parameters) !!}'>{{ $t->name }} ({{ $t->stage }})</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Objek *</label>
<select name="reference_kind" class="form-select"><option value="production">Produksi</option><option value="receipt">Penerimaan</option></select></div>
<div class="col-md-2"><label class="form-label">ID ref *</label><input name="reference_id" type="number" min="1" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Suhu (°C)</label><input name="temperature_c" type="number" step="0.1" class="form-control"/></div>
<div class="col-md-2"><label class="form-label">Foto bukti</label><input name="photo" type="file" accept="image/*" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<h4 class="mt-4">Hasil ukur *</h4>
<div id="measured" class="row g-2">
<div class="col-md-6"><input name="measured[catatan]" class="form-control" placeholder="catatan umum"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan inspeksi</button></div>
</form>
<div class="row g-3 mt-3">
<div class="col-md-6"><div class="card card-sm"><div class="card-header"><h4 class="card-title">WO berjalan (ID)</h4></div>
<div class="card-body small">@foreach($orders as $o) {{ $o->id }}:{{ $o->number }} · @endforeach</div></div></div>
<div class="col-md-6"><div class="card card-sm"><div class="card-header"><h4 class="card-title">GR terakhir (ID)</h4></div>
<div class="card-body small">@foreach($receipts as $g) {{ $g->id }}:{{ $g->number }} · @endforeach</div></div></div>
</div>
</div></div>
@endsection
@push('scripts')
<script>
function fillParams() {
const sel = document.getElementById('tpl');
const opt = sel.options[sel.selectedIndex];
const wrap = document.getElementById('measured');
wrap.innerHTML = '';
const params = opt && opt.dataset.params ? JSON.parse(opt.dataset.params) : [];
if (!params.length) {
wrap.innerHTML = '<div class="col-md-6"><input name="measured[catatan]" class="form-control" placeholder="catatan umum"/></div>';
return;
}
params.forEach((p, i) => {
wrap.insertAdjacentHTML('beforeend', `<div class="col-md-4"><label class="form-label">${p.name} ${p.spec_min ?? ''}${p.spec_min != null || p.spec_max != null ? ' – ' : ''}${p.spec_max ?? ''} ${p.unit ?? ''}</label><input name="measured[${i}]" type="number" step="0.01" class="form-control" required/></div>`);
});
}
</script>
@endpush

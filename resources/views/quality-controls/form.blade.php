@extends('layouts.app')
@section('title', 'Uji Quality Control')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('quality-controls.store') }}">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">Objek uji *</label>
<select name="reference_kind" class="form-select"><option value="production">Hasil produksi</option><option value="receipt">Penerimaan barang</option></select></div>
<div class="col-md-3"><label class="form-label">ID referensi *</label><input name="reference_id" type="number" min="1" class="form-control" required placeholder="cth. 1"/></div>
<div class="col-md-3"><label class="form-label">Tipe uji *</label>
<select name="check_type" class="form-select">@foreach(['ORGANOLEPTIC','MICROBIOLOGY','PHYSICAL','PACKAGING'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Sampel *</label><input name="sample_qty" type="number" step="0.001" min="0.001" class="form-control" required/></div>
<div class="col-md-4"><label class="form-label">Lulus *</label><input name="pass_qty" type="number" step="0.001" min="0" class="form-control" required/></div>
<div class="col-md-4"><label class="form-label">Gagal *</label><input name="fail_qty" type="number" step="0.001" min="0" class="form-control" required/></div>
</div>
<p class="text-secondary small mt-2">Hasil otomatis: gagal 0 → PASSED · lulus 0 → FAILED · selain itu CONDITIONAL. Lulus + gagal harus sama dengan sampel.</p>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan hasil uji</button></div>
</form>
<div class="row g-3 mt-3">
<div class="col-md-6"><div class="card card-sm"><div class="card-header"><h4 class="card-title">Order produksi berjalan</h4></div>
<div class="table-responsive"><table class="table table-sm card-table"><thead><tr><th>ID</th><th>Nomor</th><th>Status</th></tr></thead><tbody>
@foreach($orders as $o)<tr><td>{{ $o->id }}</td><td>{{ $o->number }}</td><td>{{ $o->status }}</td></tr>@endforeach
</tbody></table></div></div></div>
<div class="col-md-6"><div class="card card-sm"><div class="card-header"><h4 class="card-title">Penerimaan terakhir</h4></div>
<div class="table-responsive"><table class="table table-sm card-table"><thead><tr><th>ID</th><th>Nomor</th><th>Tanggal</th></tr></thead><tbody>
@foreach($receipts as $g)<tr><td>{{ $g->id }}</td><td>{{ $g->number }}</td><td>{{ $g->receipt_date }}</td></tr>@endforeach
</tbody></table></div></div></div>
</div>
</div></div>
@endsection

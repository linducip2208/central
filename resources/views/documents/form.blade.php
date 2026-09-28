@extends('layouts.app')
@section('title', 'Dokumen Baru')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">Kategori *</label>
<select name="category" class="form-select">@foreach(['SOP','WORK_INSTRUCTION','CHECKLIST','POLICY','FORM'] as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Judul *</label><input name="title" class="form-control" required/></div>
<div class="col-md-3"><label class="form-label">Versi</label><input name="version" class="form-control" value="1.0"/></div>
<div class="col-md-12"><label class="form-label">Isi *</label><textarea name="content" class="form-control" rows="8" required></textarea></div>
<div class="col-md-3"><label class="form-label">Berlaku dari</label><input name="effective_from" type="date" class="form-control"/></div>
<div class="col-md-3"><label class="form-label">Kadaluarsa</label><input name="expires_at" type="date" class="form-control"/></div>
<div class="col-md-6"><label class="form-label">Lampiran (pdf/gambar, maks 10MB)</label><input name="attachment" type="file" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan draft</button></div>
</form>
</div></div>
@endsection

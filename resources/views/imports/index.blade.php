@extends('layouts.app')
@section('title', 'Import Data')
@section('content')
<div class="row g-3">
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Import baru (preview → commit)</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('imports.preview', ['entity' => 'recipients']) }}" enctype="multipart/form-data" id="imp-form">@csrf
<div class="mb-2"><label class="form-label">Entitas</label>
<select name="entity" id="imp-entity" class="form-select" onchange="document.getElementById('imp-form').action = '/imports/' + this.value + '/preview'"><option value="recipients">recipients</option><option value="ingredients">ingredients</option><option value="schools">schools</option></select></div>
<div class="mb-2"><label class="form-label">File CSV (maks 5MB)</label><input name="file" type="file" accept=".csv" class="form-control" required/></div>
<button class="btn btn-primary" type="submit">Preview (dry-run)</button>
</form>
<div class="mt-3 text-secondary small">
<div class="fw-bold">Format header:</div>
<div><code>recipients:</code> school_code,name,identifier,grade,class,gender,allergy</div>
<div><code>ingredients:</code> code,name,category,unit_code,price,min_stock</div>
<div><code>schools:</code> code,name,level,target_portions,district</div>
</div>
</div></div>
</div>
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Riwayat import</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Entitas</th><th>File</th><th class="text-end">OK/Gagal</th><th>Status</th></tr></thead>
<tbody>
@forelse($imports as $i)
<tr><td class="text-secondary">{{ $i->created_at->format('d M H:i') }}</td><td>{{ $i->entity }}</td><td class="text-secondary small">{{ $i->filename }}</td><td class="text-end">{{ $i->imported_rows }}/{{ $i->failed_rows }}</td><td><x-badge :status="$i->status"/></td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-3">Belum ada import.</td></tr>@endforelse
</tbody></table></div></div>
</div>
</div>
@endsection

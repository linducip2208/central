@extends('layouts.app')
@section('title', 'Dokumen & SOP')
@section('actions')<a href="{{ route('documents.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Dokumen</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()"><option value="">— Semua status —</option>@foreach(['DRAFT','REVIEW','APPROVED','PUBLISHED','ARCHIVED'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select></div>
<div class="col-md-3"><select name="category" class="form-select" onchange="this.form.submit()"><option value="">— Semua kategori —</option>@foreach(['SOP','WORK_INSTRUCTION','CHECKLIST','POLICY','FORM'] as $c)<option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>@endforeach</select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Judul</th><th>Kategori</th><th>Ver</th><th>Berlaku</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($documents as $d)
<tr>
<td class="text-secondary">{{ $d->code }}</td>
<td><a href="{{ route('documents.show', $d) }}">{{ $d->title }}</a></td>
<td class="text-secondary">{{ $d->category }}</td>
<td>{{ $d->version }}</td>
<td class="text-secondary">{{ $d->effective_from ?? '-' }}</td>
<td><x-badge :status="$d->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('documents.show', $d) }}">Buka</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada dokumen"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $documents->links() }}</div>
</div></div>
@endsection

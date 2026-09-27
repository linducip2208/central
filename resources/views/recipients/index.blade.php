@extends('layouts.app')
@section('title', 'Penerima Manfaat')
@section('subtitle', $allergyCount . ' penerima memiliki catatan alergi — perhatikan saat packing')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><div class="input-icon"><span class="input-icon-addon"><i class="ti ti-search"></i></span><input type="text" name="q" class="form-control" placeholder="Nama / NIS…" value="{{ request('q') }}"/></div></div>
<div class="col-md-4"><select name="school_id" class="form-select" onchange="this.form.submit()"><option value="">— Semua sekolah —</option>@foreach($schools as $s)<option value="{{ $s->id }}" @selected(request('school_id') == $s->id)>{{ $s->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-check mt-2"><input type="checkbox" name="allergy" value="1" class="form-check-input" @checked(request('allergy')) onchange="this.form.submit()"/><span class="form-check-label">Alergi saja</span></label></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Filter</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nama</th><th>NIS</th><th>Sekolah</th><th>Kelas</th><th>L/P</th><th>Alergi</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($recipients as $r)
<tr>
<td>{{ $r->name }}</td>
<td class="text-secondary">{{ $r->identifier ?? '-' }}</td>
<td class="text-secondary">{{ $r->school->name ?? '-' }}</td>
<td class="text-secondary">{{ $r->grade }}{{ $r->class_name }}</td>
<td>{{ $r->gender ?? '-' }}</td>
<td>@if($r->allergy_notes)<span class="badge bg-red-lt">{{ $r->allergy_notes }}</span>@else<span class="text-secondary">—</span>@endif</td>
<td>@if($r->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">NONAKTIF</span>@endif</td>
<td class="text-end">
<form method="POST" action="{{ route('recipients.update', $r) }}" class="d-inline">@csrf @method('PUT')
<input type="hidden" name="name" value="{{ $r->name }}"/>
<input type="hidden" name="allergy_notes" value="{{ $r->allergy_notes }}"/>
<input type="hidden" name="is_active" value="{{ $r->is_active ? 0 : 1 }}"/>
<button class="btn btn-sm btn-white" type="submit">{{ $r->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
<form method="POST" action="{{ route('recipients.destroy', $r) }}" class="d-inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form>
</td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada penerima"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $recipients->links() }}</div>
</div></div>
@endsection

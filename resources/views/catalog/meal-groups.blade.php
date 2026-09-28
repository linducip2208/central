@extends('layouts.app')
@section('title', 'Kelompok Diet')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th class="text-end">Penerima</th><th></th></tr></thead>
<tbody>
@forelse($groups as $g)
<tr><td class="fw-bold">{{ $g->code }}</td><td>{{ $g->name }}<div class="text-secondary small">{{ $g->dietary_notes }}</div></td><td class="text-end">{{ $g->recipients_count }}</td>
<td class="text-end"><form method="POST" action="{{ route('catalog.meal-groups.destroy', $g) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form></td></tr>
@empty<tr><td colspan="4"><x-empty title="Belum ada kelompok diet"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Kelompok baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('catalog.meal-groups.store') }}">@csrf
<div class="mb-2"><label class="form-label">Kode *</label><input name="code" class="form-control" required placeholder="cth. VEGETARIAN"/></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Catatan diet</label><input name="dietary_notes" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
</div></div>
</div>
</div>
@endsection

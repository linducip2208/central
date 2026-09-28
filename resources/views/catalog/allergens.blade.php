@extends('layouts.app')
@section('title', 'Alergen')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th class="text-end">Bahan</th><th class="text-end">Penerima</th><th></th></tr></thead>
<tbody>
@forelse($allergens as $a)
<tr><td class="fw-bold">{{ $a->code }}</td><td>{{ $a->name }}<div class="text-secondary small">{{ $a->description }}</div></td><td class="text-end">{{ $a->ingredients_count }}</td><td class="text-end">{{ $a->recipients_count }}</td>
<td class="text-end"><form method="POST" action="{{ route('catalog.allergens.destroy', $a) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form></td></tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada alergen"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Alergen baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('catalog.allergens.store') }}">@csrf
<div class="mb-2"><label class="form-label">Kode *</label><input name="code" class="form-control" required placeholder="cth. SUSU"/></div>
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Deskripsi</label><input name="description" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Tambah</button>
</form>
<p class="text-secondary small mt-2">Tautkan alergen ke bahan di form bahan, dan ke penerima di halaman recipients (kolom alergi) atau via matriks di bawah pada fase lanjutan.</p>
</div></div>
</div>
</div>
@endsection

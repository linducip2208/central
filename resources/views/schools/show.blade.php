@extends('layouts.app')
@section('title', $school->name)
@section('subtitle', $school->code . ' · ' . $school->level)
@section('actions')
<a href="{{ route('schools.edit', $school) }}" class="btn btn-white">Ubah</a>
<a href="{{ route('schools.index') }}" class="btn btn-ghost-secondary">Kembali</a>
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<div class="mb-2"><x-badge :status="$school->status"/></div>
<dl class="row small">
<dt class="col-5">NPSN</dt><dd class="col-7">{{ $school->npsn ?? '-' }}</dd>
<dt class="col-5">Siswa</dt><dd class="col-7">{{ number_format($school->student_count) }}</dd>
<dt class="col-5">Target porsi</dt><dd class="col-7">{{ number_format($school->target_portions) }}/hari</dd>
<dt class="col-5">Jarak</dt><dd class="col-7">{{ $school->distance_km }} km</dd>
<dt class="col-5">PJ</dt><dd class="col-7">{{ $school->pic_name ?? '-' }} ({{ $school->pic_phone ?? '-' }})</dd>
<dt class="col-5">Alamat</dt><dd class="col-7">{{ $school->address ?? '-' }}</dd>
</dl>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Tambah penerima</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('schools.recipients.store', $school) }}">@csrf
<div class="mb-2"><input name="name" class="form-control" placeholder="Nama siswa *" required/></div>
<div class="row g-2">
<div class="col-6"><input name="identifier" class="form-control" placeholder="NIS/NISN"/></div>
<div class="col-3"><input name="grade" class="form-control" placeholder="Kelas"/></div>
<div class="col-3"><select name="gender" class="form-select"><option value="">L/P</option><option value="L">L</option><option value="P">P</option></select></div>
</div>
<div class="mt-2"><input name="allergy_notes" class="form-control" placeholder="Catatan alergi"/></div>
<button class="btn btn-primary btn-sm mt-2" type="submit">Tambah</button>
</form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Penerima ({{ $school->recipients->count() }} ditampilkan)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Alergi</th><th></th></tr></thead>
<tbody>
@forelse($school->recipients as $r)
<tr><td>{{ $r->name }}</td><td class="text-secondary">{{ $r->identifier ?? '-' }}</td><td class="text-secondary">{{ $r->grade }}{{ $r->class_name }}</td><td class="text-secondary">{{ $r->allergy_notes ?? '-' }}</td>
<td class="text-end"><form method="POST" action="{{ route('recipients.destroy', $r) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form></td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-3">Belum ada penerima.</td></tr>@endforelse
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Pengiriman terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th class="text-end">Terkirim</th><th>Status</th></tr></thead>
<tbody>
@forelse($school->deliveries as $d)
<tr><td><a href="{{ route('deliveries.show', $d) }}">{{ $d->number }}</a></td><td class="text-secondary">{{ $d->delivery_date }}</td><td class="text-end">{{ number_format($d->qty_delivered) }}</td><td><x-badge :status="$d->status"/></td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Belum ada pengiriman.</td></tr>@endforelse
</tbody></table></div></div>
</div>
</div>
@endsection

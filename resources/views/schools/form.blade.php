@extends('layouts.app')
@section('title', ($school->exists ? 'Ubah' : 'Tambah') . ' Sekolah')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ $school->exists ? route('schools.update', $school) : route('schools.store') }}">
@csrf @if($school->exists) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama sekolah *</label><input name="name" class="form-control" value="{{ old('name', $school->name) }}" required/></div>
<div class="col-md-3"><label class="form-label">Jenjang *</label>
<select name="level" class="form-select">@foreach(['PAUD','TK','SD','SMP','SMA','SMK','SLB'] as $l)<option value="{{ $l }}" @selected(old('level', $school->level) === $l)>{{ $l }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">NPSN</label><input name="npsn" class="form-control" value="{{ old('npsn', $school->npsn) }}"/></div>
<div class="col-md-6"><label class="form-label">Central kitchen</label>
<select name="central_kitchen_id" class="form-select"><option value="">—</option>@foreach($kitchens as $k)<option value="{{ $k->id }}" @selected(old('central_kitchen_id', $school->central_kitchen_id) == $k->id)>{{ $k->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Jumlah siswa *</label><input name="student_count" type="number" min="0" class="form-control" value="{{ old('student_count', $school->student_count ?? 0) }}" required/></div>
<div class="col-md-3"><label class="form-label">Target porsi/hari *</label><input name="target_portions" type="number" min="0" class="form-control" value="{{ old('target_portions', $school->target_portions ?? 0) }}" required/></div>
<div class="col-md-8"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2">{{ old('address', $school->address) }}</textarea></div>
<div class="col-md-2"><label class="form-label">Kecamatan</label><input name="district" class="form-control" value="{{ old('district', $school->district) }}"/></div>
<div class="col-md-2"><label class="form-label">Kota</label><input name="city" class="form-control" value="{{ old('city', $school->city) }}"/></div>
<div class="col-md-4"><label class="form-label">Penanggung jawab</label><input name="pic_name" class="form-control" value="{{ old('pic_name', $school->pic_name) }}"/></div>
<div class="col-md-4"><label class="form-label">Telepon PJ</label><input name="pic_phone" class="form-control" value="{{ old('pic_phone', $school->pic_phone) }}"/></div>
<div class="col-md-2"><label class="form-label">Jarak (km)</label><input name="distance_km" type="number" step="0.1" min="0" class="form-control" value="{{ old('distance_km', $school->distance_km ?? 0) }}"/></div>
<div class="col-md-2"><label class="form-label">Status *</label>
<select name="status" class="form-select">@foreach(['ACTIVE','INACTIVE'] as $s)<option value="{{ $s }}" @selected(old('status', $school->status ?? 'ACTIVE') === $s)>{{ $s }}</option>@endforeach</select></div>
</div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan</button><a href="{{ route('schools.index') }}" class="btn btn-white">Batal</a></div>
</form>
</div></div>
@endsection

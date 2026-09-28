@extends('layouts.app')
@section('title', 'Pencarian')
@section('subtitle', $q !== '' ? 'Hasil untuk "' . $q . '"' : 'Ketik minimal 2 karakter')
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" action="{{ route('search.index') }}">
<div class="input-group input-group-lg">
<input name="q" class="form-control" placeholder="Cari sekolah, supplier, produk, batch, PO, delivery…" value="{{ $q }}" autofocus/>
<button class="btn btn-primary" type="submit">Cari</button>
</div>
</form>
</div></div>
@if($q !== '')
<div class="row g-3">
@forelse($groups as $title => $rows)
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">{{ $title }} ({{ count($rows) }})</h3></div>
<div class="list-group list-group-flush">
@foreach($rows as $r)
<a href="{{ $r['url'] }}" class="list-group-item list-group-item-action"><div class="fw-bold">{{ $r['label'] }}</div><div class="text-secondary small">{{ $r['sub'] }}</div></a>
@endforeach
</div></div>
</div>
@empty
<div class="col-12"><x-empty title="Tidak ditemukan" subtitle="Coba kata kunci lain."/></div>
@endforelse
</div>
@endif
@endsection

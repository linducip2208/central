@extends('layouts.app')
@section('title', 'Siklus Menu')
@section('content')
<div class="row g-3">
<div class="col-lg-8">
@foreach($cycles as $c)
<div class="card mb-3"><div class="card-header"><h3 class="card-title">{{ $c->code }} — {{ $c->name }} <span class="badge bg-blue-lt ms-1">{{ $c->cycle_days }} hari</span> <span class="ms-1"><x-badge :status="$c->status"/></span></h3>
<a href="{{ route('menu-cycles.show', $c) }}" class="btn btn-sm btn-white ms-auto">Kelola</a></div>
<div class="card-body">
<div class="row g-1">
@for($d = 1; $d <= $c->cycle_days; $d++)
@php $day = $c->days->firstWhere('day_no', $d); @endphp
<div class="col-md-2"><div class="border rounded p-1 text-center small @if(!$day) bg-yellow-lt @endif"><div class="fw-bold">H{{ $d }}</div><div>{{ $day?->menu->name ?? '—' }}</div></div></div>
@endfor
</div>
</div></div>
@endforeach
</div>
<div class="col-lg-4">
<div class="card"><div class="card-header"><h3 class="card-title">Siklus baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('menu-cycles.store') }}">@csrf
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required placeholder="cth. Siklus 10 hari Agustus"/></div>
<div class="mb-2"><label class="form-label">Jumlah hari *</label><input name="cycle_days" type="number" min="1" max="31" value="5" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">Mulai *</label><input name="start_date" type="date" class="form-control" value="{{ now()->toDateString() }}" required/></div>
<button class="btn btn-primary" type="submit">Buat</button>
</form>
</div></div>
</div>
</div>
@endsection

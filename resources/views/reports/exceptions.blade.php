@extends('layouts.app')
@section('title', 'Laporan Eksepsi')
@section('subtitle', 'Semua anomali operasional terbaru — klik untuk drill-down')
@section('content')
<div class="row g-3">
@foreach($items as $title => $rows)
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">{{ $title }} ({{ $rows->count() }})</h3></div>
<div class="list-group list-group-flush">
@forelse($rows as $r)
<a href="{{ $r['url'] }}" class="list-group-item list-group-item-action">{{ $r['label'] }}</a>
@empty<div class="list-group-item text-secondary">Bersih — tidak ada.</div>@endforelse
</div></div>
</div>
@endforeach
</div>
@endsection

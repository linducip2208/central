@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<div class="row row-deck row-cards">
@php
$cards = [
['Stok & nilai persediaan', 'reports.stock', 'ti-box', 'Posisi stok per gudang + valuasi.', []],
['Produksi', 'reports.production', 'ti-chef-hat', 'Hasil produksi per periode.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Pengiriman & fulfillment', 'reports.delivery', 'ti-truck', 'Terkirim vs rencana per sekolah.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Keuangan', 'reports.financial', 'ti-coins', 'Biaya produksi, belanja, rugi waste.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Kedaluarsa', 'reports.expiry', 'ti-alarm', 'Batch mendekati expired.', ['days' => 30]],
];
@endphp
@foreach($cards as [$title, $route, $icon, $desc, $params])
<div class="col-md-4">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center mb-2"><span class="avatar bg-blue-lt me-2"><i class="ti {{ $icon }}"></i></span><h3 class="card-title mb-0">{{ $title }}</h3></div>
<p class="text-secondary">{{ $desc }}</p>
<a href="{{ route($route, $params) }}" class="btn btn-white">Buka laporan</a>
</div></div>
</div>
@endforeach
</div>
@endsection

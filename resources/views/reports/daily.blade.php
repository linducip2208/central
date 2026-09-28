@extends('layouts.app')
@section('title', 'Laporan Harian Dapur')
@section('subtitle', $date)
@section('content')
<div class="card mb-3"><div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-3"><input name="date" type="date" class="form-control" value="{{ $date }}"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
<div class="col-md-auto"><button class="btn btn-white" type="button" onclick="window.print()">Cetak</button></div>
</form>
</div></div>
<div class="row row-deck row-cards mb-3">
@foreach([['Porsi diproduksi', number_format($summary['portions'])], ['Terkirim', number_format($summary['delivered']).'/'.number_format($summary['planned_delivery'])], ['Service level', $summary['service_level'].'%'], ['Rugi waste', mbg_currency($summary['waste_loss'])], ['Biaya produksi', mbg_currency($summary['production_cost'])], ['Mutasi stok', number_format($movements)]] as [$l, $v])
<div class="col-sm-4 col-lg-2"><div class="card"><div class="card-body p-2 text-center"><div class="text-secondary small">{{ $l }}</div><div class="h3 mb-0">{{ $v }}</div></div></div></div>
@endforeach
</div>
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Produksi</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>WO</th><th>Produk</th><th class="text-end">Hasil</th><th>Status</th></tr></thead>
<tbody>
@forelse($productions as $p)
<tr><td>{{ $p->number }}</td><td class="text-secondary">{{ $p->product->name ?? '' }}</td><td class="text-end">{{ number_format($p->produced_qty, 0) }}</td><td><x-badge :status="$p->status"/></td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-2">Tidak ada produksi.</td></tr>@endforelse
</tbody></table></div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Delivery</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Sekolah</th><th class="text-end">Terkirim</th><th>Status</th></tr></thead>
<tbody>
@forelse($deliveries as $d)
<tr><td>{{ $d->number }}</td><td class="text-secondary">{{ $d->school->name ?? '' }}</td><td class="text-end">{{ number_format($d->qty_delivered) }}</td><td><x-badge :status="$d->status"/></td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-2">Tidak ada delivery.</td></tr>@endforelse
</tbody></table></div></div>
</div>
</div>
@endsection

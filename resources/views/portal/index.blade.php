@extends('layouts.app')
@section('title', 'Portal Sekolah')
@section('subtitle', $pending->count() . ' delivery menunggu konfirmasi')
@section('actions')<a href="{{ route('portal.complaints') }}" class="btn btn-white">Keluhan</a>@endsection
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Delivery terbaru</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Nomor</th><th>Sekolah</th><th class="text-end">Rencana</th><th class="text-end">Diterima (kurir)</th><th>Konfirmasi</th><th></th></tr></thead>
<tbody>
@forelse($deliveries as $d)
<tr>
<td class="text-secondary">{{ $d->delivery_date }}</td>
<td><a href="{{ route('portal.show', $d) }}">{{ $d->number }}</a></td>
<td>{{ $d->school->name ?? '' }}</td>
<td class="text-end">{{ number_format($d->qty_planned) }}</td>
<td class="text-end">{{ number_format($d->qty_delivered) }}</td>
<td>@if($d->confirmation)<span class="badge bg-green-lt">SUDAH</span>@else<span class="badge bg-yellow-lt">MENUNGGU</span>@endif</td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('portal.show', $d) }}">Konfirmasi</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada delivery"/></td></tr>
@endforelse
</tbody></table></div></div>
@endsection

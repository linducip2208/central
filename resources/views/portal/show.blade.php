@extends('layouts.app')
@section('title', 'Konfirmasi ' . $delivery->number)
@section('subtitle', ($delivery->school->name ?? '') . ' · rencana ' . number_format($delivery->qty_planned) . ' porsi')
@section('content')
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Item terkirim</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Produk</th><th class="text-end">Rencana</th><th class="text-end">Terkirim</th></tr></thead>
<tbody>
@foreach($delivery->items as $it)
<tr><td>{{ $it->product->name ?? '' }}</td><td class="text-end">{{ number_format($it->qty_planned) }}</td><td class="text-end">{{ number_format($it->qty_delivered) }}</td></tr>
@endforeach
</tbody></table></div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Formulir konfirmasi sekolah</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('portal.confirm', $delivery) }}">@csrf
<div class="row g-2">
<div class="col-md-4"><label class="form-label">Diterima baik *</label><input name="received_qty" type="number" min="0" max="{{ $delivery->qty_planned }}" value="{{ $delivery->confirmation->received_qty ?? $delivery->qty_delivered }}" class="form-control" required/></div>
<div class="col-md-4"><label class="form-label">Ditolak</label><input name="rejected_qty" type="number" min="0" value="{{ $delivery->confirmation->rejected_qty ?? 0 }}" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Kehadiran siswa</label><input name="attendance" type="number" min="0" value="{{ $delivery->confirmation->attendance ?? '' }}" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Keluhan / insiden</label><textarea name="complaint" class="form-control" rows="2">{{ $delivery->confirmation->complaint ?? '' }}</textarea></div>
<div class="col-md-12"><label class="form-label">Masukan</label><textarea name="feedback" class="form-control" rows="2">{{ $delivery->confirmation->feedback ?? '' }}</textarea></div>
</div>
<button class="btn btn-success mt-2" type="submit">Kirim konfirmasi</button>
</form>
</div></div>
</div>
</div>
@endsection

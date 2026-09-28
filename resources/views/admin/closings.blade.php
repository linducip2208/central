@extends('layouts.app')
@section('title', 'Tutup Periode')
@section('subtitle', 'Transaksi bertanggal sebelum tanggal tutup tidak dapat diposting (GR, produksi, delivery, waste, adjustment, transfer, opname, retur)')
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Riwayat penutupan</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal tutup</th><th>Dapur</th><th>Oleh</th><th>Dibuat</th></tr></thead>
<tbody>
@forelse($closings as $c)
<tr><td>sebelum <strong>{{ $c->closed_before }}</strong></td><td class="text-secondary">{{ $c->centralKitchen->name ?? 'Semua dapur' }}</td><td class="text-secondary">{{ $c->created_by }}</td><td class="text-secondary">{{ $c->created_at->format('d M Y') }}</td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Belum ada penutupan.</td></tr>@endforelse
</tbody></table></div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Tutup periode baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('closings.store') }}">@csrf
<div class="mb-2"><label class="form-label">Dapur (kosongkan = seluruh organisasi)</label>
<select name="central_kitchen_id" class="form-select"><option value="">— Semua —</option>@foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Kunci transaksi sebelum *</label><input name="closed_before" type="date" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}" required/></div>
<button class="btn btn-warning" type="submit" onclick="return confirm('Kunci periode? Tidak dapat dibatalkan dari UI.')">Tutup periode</button>
</form>
</div></div>
</div>
</div>
@endsection

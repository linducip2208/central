@extends('layouts.app')
@section('title', 'Riwayat Harga Beli')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-6"><select name="ingredient_id" class="form-select" onchange="this.form.submit()"><option value="">— pilih bahan —</option>@foreach($ingredients as $i)<option value="{{ $i->id }}" @selected(request('ingredient_id') == $i->id)>{{ $i->name }}</option>@endforeach</select></div>
</form>
@if($selected)
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>GR</th><th>Supplier</th><th class="text-end">Qty</th><th class="text-end">Harga</th></tr></thead>
<tbody>
@forelse($history as $h)
<tr><td class="text-secondary">{{ $h['date'] }}</td><td>{{ $h['ref'] }}</td><td class="text-secondary">{{ $h['supplier'] }}</td><td class="text-end">{{ number_format($h['qty'], 2) }}</td><td class="text-end">{{ mbg_currency($h['price']) }}</td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-3">Belum ada riwayat pembelian.</td></tr>@endforelse
</tbody></table></div>
@else
<p class="text-secondary">Pilih bahan untuk melihat tren harga beli dari goods receipt aktual.</p>
@endif
</div></div>
@endsection

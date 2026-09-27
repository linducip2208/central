@extends('layouts.app')
@section('title', 'Waste')
@section('actions')<a href="{{ route('wastes.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Catat waste</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="reason" class="form-select" onchange="this.form.submit()"><option value="">— Semua alasan —</option>@foreach(['EXPIRED','SPOILED','OVER_PRODUCTION','QC_REJECT','OTHER'] as $r)<option value="{{ $r }}" @selected(request('reason') === $r)>{{ $r }}</option>@endforeach</select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th>Item</th><th class="text-end">Qty</th><th>Alasan</th><th class="text-end">Kerugian</th></tr></thead>
<tbody>
@forelse($wastes as $w)
<tr>
<td class="text-secondary">{{ $w->number }}</td>
<td class="text-secondary">{{ $w->waste_date }}</td>
<td class="text-secondary">{{ $w->item_type }} #{{ $w->item_id }}</td>
<td class="text-end">{{ number_format($w->qty, 2) }}</td>
<td><span class="badge bg-red-lt">{{ $w->reason }}</span></td>
<td class="text-end">{{ mbg_currency($w->cost_loss) }}</td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada waste"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $wastes->links() }}</div>
</div></div>
@endsection

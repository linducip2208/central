@extends('layouts.app')
@section('title', 'Supplier Invoice')
@section('actions')<a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Invoice</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="match" class="form-select" onchange="this.form.submit()"><option value="">— Semua match —</option><option value="MATCHED" @selected(request('match') === 'MATCHED')>MATCHED</option><option value="VARIANCE" @selected(request('match') === 'VARIANCE')>VARIANCE</option><option value="UNMATCHED" @selected(request('match') === 'UNMATCHED')>UNMATCHED</option></select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>No. supplier</th><th>Supplier</th><th class="text-end">Total</th><th>Match</th><th>Bayar</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($invoices as $inv)
<tr>
<td><a href="{{ route('invoices.show', $inv) }}">{{ $inv->number }}</a></td>
<td class="text-secondary">{{ $inv->supplier_invoice_no }}</td>
<td class="text-secondary">{{ $inv->supplier->name ?? '' }}</td>
<td class="text-end">{{ mbg_currency($inv->grand_total) }}</td>
<td>@if($inv->match_status === 'MATCHED')<span class="badge bg-green-lt">MATCHED</span>@else<span class="badge bg-yellow-lt">{{ $inv->match_status }}</span>@endif</td>
<td>@if($inv->payment_status === 'PAID')<span class="badge bg-green-lt">PAID</span>@else<span class="badge bg-secondary-lt">UNPAID</span>@endif</td>
<td><x-badge :status="$inv->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('invoices.show', $inv) }}">Verifikasi</a></td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada invoice"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $invoices->links() }}</div>
</div></div>
@endsection

@extends('layouts.app')
@section('title', 'Utang Supplier (AP Aging)')
@section('subtitle', 'Total belum bayar: ' . mbg_currency($total) . ' · lewat jatuh tempo: ' . mbg_currency($overdueTotal))
@section('content')
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Invoice</th><th>Supplier</th><th>Jatuh tempo</th><th class="text-end">Nominal</th><th>Status</th></tr></thead>
<tbody>
@forelse($invoices as $inv)
<tr>
<td><a href="{{ route('invoices.show', $inv) }}">{{ $inv->number }}</a><div class="text-secondary small">{{ $inv->supplier_invoice_no }}</div></td>
<td class="text-secondary">{{ $inv->supplier->name ?? '' }}</td>
<td>{{ $inv->due_date }} <span class="text-secondary small">({{ $inv->bucket }})</span></td>
<td class="text-end">{{ mbg_currency($inv->grand_total) }}</td>
<td>@if($inv->is_overdue)<span class="badge bg-red-lt">LEWAT TEMPO</span>@else<span class="badge bg-yellow-lt">MENUNGGU</span>@endif</td>
</tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-3">Tidak ada utang jatuh tempo.</td></tr>@endforelse
</tbody></table></div>
</div></div>
@endsection

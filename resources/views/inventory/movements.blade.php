@extends('layouts.app')
@section('title', 'Ledger Mutasi Stok')
@section('subtitle', 'Sumber kebenaran tunggal — tidak pernah diubah/dihapus')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><select name="warehouse_id" class="form-select" onchange="this.form.submit()"><option value="">— Semua gudang —</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse_id') == $w->id)>{{ $w->name }}</option>@endforeach</select></div>
<div class="col-md-4"><select name="type" class="form-select" onchange="this.form.submit()"><option value="">— Semua tipe —</option>@foreach($types as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>@endforeach</select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Tipe</th><th>Item</th><th>Batch</th><th>Referensi</th><th class="text-end">Sebelum</th><th class="text-end">Mutasi</th><th class="text-end">Sesudah</th></tr></thead>
<tbody>
@forelse($movements as $m)
<tr>
<td class="text-secondary">{{ $m->created_at->format('d M Y H:i') }}</td>
<td><span class="badge bg-blue-lt">{{ $m->movement_type }}</span></td>
<td class="text-secondary">{{ $m->item_type }} #{{ $m->item_id }}</td>
<td class="text-secondary">{{ $m->batch->batch_no ?? '-' }}</td>
<td class="text-secondary">{{ $m->reference_no ?? '-' }}</td>
<td class="text-end text-secondary">{{ number_format($m->stock_before, 2) }}</td>
<td class="text-end {{ $m->direction === 'IN' ? 'text-green' : 'text-red' }}">{{ $m->direction === 'IN' ? '+' : '-' }}{{ number_format($m->qty, 2) }}</td>
<td class="text-end fw-bold">{{ number_format($m->stock_after, 2) }}</td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada mutasi"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $movements->links() }}</div>
</div></div>
@endsection

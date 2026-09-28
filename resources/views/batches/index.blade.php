@extends('layouts.app')
@section('title', 'Batch & Expired')
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="q" class="form-control" placeholder="No. batch…" value="{{ request('q') }}"/></div>
<div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()"><option value="">— Semua status —</option>@foreach(['AVAILABLE','BLOCKED','EXPIRED','DEPLETED'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select></div>
<div class="col-md-3"><select name="expiring" class="form-select" onchange="this.form.submit()"><option value="">— Kedaluwarsa —</option><option value="7" @selected(request('expiring') == '7')>≤ 7 hari</option><option value="30" @selected(request('expiring') == '30')>≤ 30 hari</option><option value="90" @selected(request('expiring') == '90')>≤ 90 hari</option></select></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Filter</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Batch</th><th>Item</th><th>Gudang</th><th>Produksi</th><th>Umur</th><th>Expired</th><th class="text-end">Sisa</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($batches as $b)
<tr>
<td class="fw-bold">{{ $b->batch_no }}</td>
<td>{{ $b->item_name ?? $b->item_type.' #'.$b->item_id }}</td>
<td class="text-secondary">{{ $b->warehouse->name ?? '-' }}</td>
<td class="text-secondary">{{ $b->production_date ?? '-' }}</td>
<td class="text-secondary">{{ $b->created_at->diffInDays(now()) }} hr</td>
<td>@if($b->expiry_date)<span class="badge {{ \Carbon\Carbon::parse($b->expiry_date)->isPast() ? 'bg-red-lt' : (\Carbon\Carbon::parse($b->expiry_date)->diffInDays(now()) <= 30 ? 'bg-yellow-lt' : 'bg-green-lt') }}">{{ $b->expiry_date }}</span>@else<span class="text-secondary">—</span>@endif</td>
<td class="text-end">{{ number_format($b->remaining_qty, 2) }}</td>
<td><x-badge :status="$b->status"/></td>
<td class="text-end">
@if($b->status === 'AVAILABLE')<form method="POST" action="{{ route('batches.block', $b) }}" class="d-inline">@csrf<button class="btn btn-sm btn-white" type="submit">Blokir</button></form>@endif
@if($b->status === 'BLOCKED')<form method="POST" action="{{ route('batches.unblock', $b) }}" class="d-inline">@csrf<button class="btn btn-sm btn-white" type="submit">Buka</button></form>@endif
</td>
</tr>
@empty<tr><td colspan="9"><x-empty title="Tidak ada batch"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $batches->links() }}</div>
</div></div>
@endsection

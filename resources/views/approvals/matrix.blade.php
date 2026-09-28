@extends('layouts.app')
@section('title', 'Matriks Persetujuan')
@section('subtitle', 'Level berdasarkan nominal · tanpa matriks = cukup permission route')
@section('actions')<a href="{{ route('approvals.delegations') }}" class="btn btn-white">Delegasi</a><a href="{{ route('approvals.inbox') }}" class="btn btn-ghost-secondary">Inbox</a>@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Entitas</th><th class="text-end">Min. nominal</th><th class="text-end">Level</th><th>Peran</th><th></th></tr></thead>
<tbody>
@forelse($matrices as $m)
<tr><td>{{ $m->approvable_type }}</td><td class="text-end">{{ mbg_currency($m->min_amount) }}</td><td class="text-end">L{{ $m->level }}</td><td>{{ $m->role }}</td>
<td class="text-end"><form method="POST" action="{{ route('approvals.matrix.destroy', $m) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form></td></tr>
@empty<tr><td colspan="5"><x-empty title="Belum ada matriks — semua approval cukup permission"/></td></tr>@endforelse
</tbody></table></div>
</div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Aturan baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('approvals.matrix.store') }}">@csrf
<div class="mb-2"><label class="form-label">Entitas *</label>
<select name="approvable_type" class="form-select">@foreach(['PurchaseRequest','PurchaseOrder','SupplierInvoice','Recall','Bom','Menu','StockOpname'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
<div class="row g-2">
<div class="col-6"><label class="form-label">Min. nominal *</label><input name="min_amount" type="number" min="0" class="form-control" required/></div>
<div class="col-6"><label class="form-label">Level *</label><input name="level" type="number" min="1" max="5" value="1" class="form-control" required/></div>
</div>
<div class="mb-2 mt-2"><label class="form-label">Peran *</label>
<select name="role" class="form-select">@foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach</select></div>
<button class="btn btn-primary" type="submit">Simpan</button>
</form>
</div></div>
</div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Supplier')
@section('actions')<a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Supplier</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['ACTIVE','INACTIVE']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Kontak</th><th>Telepon</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($suppliers as $s)
<tr>
<td class="text-secondary">{{ $s->code }}</td>
<td><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
<td>{{ $s->category }}</td>
<td class="text-secondary">{{ $s->contact_person ?? '-' }}</td>
<td class="text-secondary">{{ $s->phone ?? '-' }}</td>
<td><x-badge :status="$s->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('suppliers.show', $s) }}">Detail</a></td>
</tr>
@empty
<tr><td colspan="7"><x-empty title="Belum ada supplier"/></td></tr>
@endforelse
</tbody>
</table></div>
<div class="mt-3">{{ $suppliers->links() }}</div>
</div></div>
@endsection

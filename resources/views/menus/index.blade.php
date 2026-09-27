@extends('layouts.app')
@section('title', 'Menu')
@section('actions')<a href="{{ route('menus.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Menu</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['DRAFT','APPROVED','CANCELLED']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Nama</th><th>Tipe</th><th class="text-end">Porsi rencana</th><th class="text-end">Budget/porsi</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($menus as $m)
<tr>
<td class="text-secondary">{{ \Carbon\Carbon::parse($m->menu_date)->format('d M Y') }}</td>
<td><a href="{{ route('menus.show', $m) }}">{{ $m->name }}</a><div class="text-secondary small">{{ $m->code }}</div></td>
<td>{{ $m->meal_type }}</td>
<td class="text-end">{{ number_format($m->planned_portions) }}</td>
<td class="text-end">{{ mbg_currency($m->budget_per_portion) }}</td>
<td><x-badge :status="$m->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('menus.show', $m) }}">Detail</a></td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada menu"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $menus->links() }}</div>
</div></div>
@endsection

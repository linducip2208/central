@extends('layouts.app')
@section('title', 'Nutrisi vs Target')
@section('subtitle', 'Target per porsi — Kalori 600 · Protein 20g · Karbo 80g · Lemak 18g')
@section('content')
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Menu</th><th class="text-end">Kalori</th><th class="text-end">Protein</th><th class="text-end">Karbo</th><th class="text-end">Lemak</th><th>Status</th></tr></thead>
<tbody>
@foreach($menus as $m)
@php $n = $m->nutrition->first(); @endphp
<tr>
<td class="text-secondary">{{ $m->menu_date }}</td>
<td>{{ $m->name }}</td>
@if($n)
@php
$ok = $n->calories >= $targets['calories'] * 0.9 && $n->protein_g >= $targets['protein_g'] * 0.9;
@endphp
<td class="text-end">{{ $n->calories }}</td>
<td class="text-end">{{ $n->protein_g }}</td>
<td class="text-end">{{ $n->carbs_g }}</td>
<td class="text-end">{{ $n->fat_g }}</td>
<td>@if($ok)<span class="badge bg-green-lt">TERCUKUPI</span>@else<span class="badge bg-yellow-lt">DI BAWAH TARGET</span>@endif</td>
@else
<td colspan="4" class="text-center text-secondary">Belum ada data gizi</td><td><span class="badge bg-secondary-lt">—</span></td>
@endif
</tr>
@endforeach
</tbody></table></div>
</div></div>
@endsection

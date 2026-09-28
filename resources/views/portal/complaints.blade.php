@extends('layouts.app')
@section('title', 'Keluhan Sekolah')
@section('content')
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Sekolah</th><th>Delivery</th><th>Keluhan</th><th>Masukan</th></tr></thead>
<tbody>
@forelse($confirmations as $c)
<tr>
<td class="text-secondary">{{ $c->created_at->format('d M Y') }}</td>
<td>{{ $c->school->name ?? '' }}</td>
<td class="text-secondary">{{ $c->delivery->number ?? '' }}</td>
<td>{{ $c->complaint }}</td>
<td class="text-secondary">{{ $c->feedback ?? '—' }}</td>
</tr>
@empty<tr><td colspan="5"><x-empty title="Tidak ada keluhan. Bagus!"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $confirmations->links() }}</div>
</div></div>
@endsection

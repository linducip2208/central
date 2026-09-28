@extends('layouts.app')
@section('title', 'Checklist Higiene & Sanitasi')
@section('actions')<a href="{{ route('hygiene.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Ceklis baru</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="type" class="form-select" onchange="this.form.submit()"><option value="">— Semua tipe —</option>@foreach(['CLEANING','SANITATION','EQUIPMENT'] as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>@endforeach</select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Tipe</th><th>Area</th><th class="text-end">Lulus</th><th>Hasil</th><th>Oleh</th></tr></thead>
<tbody>
@forelse($checks as $c)
@php $pass = collect($c->items)->where('pass', true)->count(); $total = count($c->items); @endphp
<tr>
<td class="text-secondary">{{ $c->checked_at->format('d M Y H:i') }}</td>
<td>{{ $c->check_type }}</td>
<td>{{ $c->area }}</td>
<td class="text-end">{{ $pass }}/{{ $total }}</td>
<td><x-badge :status="$c->result"/></td>
<td class="text-secondary">{{ $c->checker->name ?? '' }}</td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada checklist"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $checks->links() }}</div>
</div></div>
@endsection

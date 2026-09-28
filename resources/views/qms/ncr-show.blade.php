@extends('layouts.app')
@section('title', 'NCR ' . $ncr->number)
@section('actions')
@if($ncr->status === 'OPEN')
<form method="POST" action="{{ route('ncrs.close', $ncr) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Tutup NCR</button></form>
@endif
@endsection
@section('content')
<div class="row g-3">
<div class="col-lg-5">
<div class="card"><div class="card-body">
<p>{{ $ncr->description }}</p>
<dl class="row small mb-0">
<dt class="col-4">Kategori</dt><dd class="col-8">{{ $ncr->category }} · {{ $ncr->severity }}</dd>
<dt class="col-4">Disposisi</dt><dd class="col-8">{{ $ncr->disposition }}</dd>
<dt class="col-4">Batch</dt><dd class="col-8">@if($ncr->batch)<a href="{{ route('trace.batch', $ncr->batch) }}">{{ $ncr->batch->batch_no }}</a> (<x-badge :status="$ncr->batch->status"/>)@else — @endif</dd>
<dt class="col-4">Status</dt><dd class="col-8"><x-badge :status="$ncr->status"/></dd>
</dl>
</div></div>
</div>
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">CAPA (Corrective & Preventive)</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('ncrs.capa', $ncr) }}">@csrf
<div class="row g-2">
<div class="col-md-3"><select name="action_type" class="form-select"><option value="CORRECTIVE">CORRECTIVE</option><option value="PREVENTIVE">PREVENTIVE</option></select></div>
<div class="col-md-9"><input name="action" class="form-control" placeholder="Tindakan *" required/></div>
<div class="col-md-6"><input name="owner_id" type="hidden"/><input name="due_date" type="date" class="form-control"/></div>
<div class="col-md-6"><button class="btn btn-primary" type="submit">Tambah CAPA</button></div>
</div>
</form>
</div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tipe</th><th>Tindakan</th><th>Jatuh tempo</th><th>Status</th><th></th></tr></thead>
<tbody>
@foreach($ncr->capaActions as $c)
<tr><td>{{ $c->action_type }}</td><td>{{ $c->action }}</td><td class="text-secondary">{{ $c->due_date ?? '-' }}</td><td><x-badge :status="$c->status"/></td>
<td class="text-end">@if($c->status !== 'DONE')<form method="POST" action="{{ route('capa.complete', $c) }}">@csrf<button class="btn btn-sm btn-success" type="submit">Selesai</button></form>@endif</td></tr>
@endforeach
</tbody></table></div></div>
</div>
</div>
@endsection

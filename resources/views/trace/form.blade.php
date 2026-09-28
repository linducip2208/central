@extends('layouts.app')
@section('title', 'Traceability')
@section('subtitle', 'Lacak genealogy batch maju (forward) & mundur (backward)')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('trace.lookup') }}">
@csrf
<div class="row g-2">
<div class="col-md-8"><label class="form-label">Nomor batch *</label><input name="batch_no" class="form-control form-control-lg" placeholder="cth. DEMO-ABC123" required autofocus/></div>
<div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary btn-lg w-100" type="submit">Lacak</button></div>
</div>
</form>
</div></div>
@endsection

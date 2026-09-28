@extends('layouts.app')
@section('title', 'Buat Recall')
@section('subtitle', 'Batch terdampak dihitung otomatis dari genealogy forward')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('recalls.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Batch pemicu *</label>
<select name="trigger_batch_id" class="form-select" required>@foreach($batches as $b)<option value="{{ $b->id }}">{{ $b->batch_no }} (sisa {{ number_format($b->remaining_qty, 2) }})</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Alasan *</label>
<select name="reason" class="form-select">@foreach(['CONTAMINATION','ALLERGEN','FOREIGN_OBJECT','SPOILAGE','OTHER'] as $r)<option value="{{ $r }}">{{ $r }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Severity *</label>
<select name="severity" class="form-select"><option value="CLASS_I">CLASS_I (berat)</option><option value="CLASS_II" selected>CLASS_II (sedang)</option><option value="CLASS_III">CLASS_III (ringan)</option></select></div>
<div class="col-md-12"><label class="form-label">Deskripsi *</label><textarea name="description" class="form-control" rows="3" required></textarea></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-danger" type="submit">Buat recall</button></div>
</form>
</div></div>
@endsection

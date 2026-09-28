@extends('layouts.app')
@section('title', 'Checklist Baru')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('hygiene.store') }}" enctype="multipart/form-data">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Tipe *</label>
<select name="check_type" id="check-type" class="form-select" onchange="renderItems()">@foreach($types as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Area *</label><input name="area" class="form-control" required placeholder="cth. Ruang masak A"/></div>
<div class="col-md-4"><label class="form-label">Foto (opsional)</label><input name="photo" type="file" accept="image/*" class="form-control"/></div>
</div>
<h4 class="mt-4">Centang yang lulus *</h4>
<div id="items" class="row g-1"></div>
<div class="mt-2"><label class="form-label">Catatan temuan</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan checklist</button></div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
const TPL = @json(\App\Http\Controllers\HygieneController::CHECKLIST);
function renderItems() {
const t = document.getElementById('check-type').value;
const wrap = document.getElementById('items');
wrap.innerHTML = TPL[t].map((label, i) => `<div class="col-md-6"><label class="form-check"><input type="checkbox" name="passed[]" value="${i}" class="form-check-input" checked/><span class="form-check-label">${label}</span></label></div>`).join('');
}
renderItems();
</script>
@endpush

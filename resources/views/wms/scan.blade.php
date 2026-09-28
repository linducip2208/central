@extends('layouts.app')
@section('title', 'Scan Barcode / QR')
@section('subtitle', 'Tempel pemindai lalu Enter · dukung barcode bin & nomor batch')
@section('content')
<div class="row g-3">
<div class="col-lg-5">
<div class="card"><div class="card-body">
<form method="GET" action="{{ route('wms.scan') }}">
<div class="input-group input-group-lg">
<input name="code" id="scan-input" class="form-control" placeholder="Scan di sini…" value="{{ request('code') }}" autocomplete="off" autofocus/>
<button class="btn btn-primary" type="submit">Cari</button>
</div>
</form>
<div class="mt-3 d-flex gap-2">
<button class="btn btn-white" type="button" onclick="startCamera()">Kamera (bila didukung)</button>
<a href="{{ route('batches.index') }}" class="btn btn-ghost-secondary">Daftar batch</a>
</div>
<video id="cam" style="display:none; width:100%" class="mt-2 rounded border"></video>
<p class="text-secondary small mt-2">Mode kamera memakai BarcodeDetector bawaan browser bila tersedia; jika tidak, gunakan pemindai fisik (keyboard-wedge).</p>
</div></div>
</div>
<div class="col-lg-7">
@if(isset($result))
@if($result['type'] === 'bin')
@php $bin = $result['bin']; @endphp
<div class="card"><div class="card-header"><h3 class="card-title">Bin: <code>{{ $bin->barcode }}</code></h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Batch</th><th>Item</th><th class="text-end">Sisa</th><th>Status</th></tr></thead>
<tbody>
@forelse($bin->batches as $b)
<tr><td><a href="{{ route('trace.batch', $b) }}">{{ $b->batch_no }}</a></td><td class="text-secondary">{{ $b->item_type }} #{{ $b->item_id }}</td><td class="text-end">{{ number_format($b->remaining_qty, 2) }}</td><td><x-badge :status="$b->status"/></td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Bin kosong.</td></tr>@endforelse
</tbody></table></div></div>
@elseif($result['type'] === 'batch')
@php $batch = $result['batch']; @endphp
<div class="card"><div class="card-body">
<h3>{{ $batch->batch_no }} <span class="ms-2"><x-badge :status="$batch->status"/></span></h3>
<dl class="row small">
<dt class="col-4">Gudang</dt><dd class="col-8">{{ $batch->warehouse->name ?? '' }}</dd>
<dt class="col-4">Bin</dt><dd class="col-8">{{ $batch->bin->barcode ?? '— belum put-away —' }}</dd>
<dt class="col-4">Sisa</dt><dd class="col-8">{{ number_format($batch->remaining_qty, 2) }}</dd>
<dt class="col-4">Expired</dt><dd class="col-8">{{ $batch->expiry_date ?? '—' }}</dd>
</dl>
<a href="{{ route('trace.batch', $batch) }}" class="btn btn-primary">Genealogy lengkap</a>
</div></div>
@else
<div class="alert alert-warning">Kode tidak ditemukan sebagai bin maupun batch.</div>
@endif
@endif
</div>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('scan-input')?.focus();
async function startCamera() {
if (!('BarcodeDetector' in window)) { alert('Browser tidak mendukung BarcodeDetector. Gunakan pemindai fisik.'); return; }
const video = document.getElementById('cam');
video.style.display = 'block';
const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
video.srcObject = stream;
await video.play();
const detector = new BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13', 'code_39'] });
const tick = async () => {
try {
const codes = await detector.detect(video);
if (codes.length) {
document.getElementById('scan-input').value = codes[0].rawValue;
stream.getTracks().forEach(t => t.stop());
document.querySelector('form').submit();
return;
}
} catch (e) {}
requestAnimationFrame(tick);
};
tick();
}
</script>
@endpush

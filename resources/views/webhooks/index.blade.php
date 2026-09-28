@extends('layouts.app')
@section('title', 'Webhooks')
@section('subtitle', 'Event keluar dengan HMAC-SHA256 signature + retry otomatis 5×')
@section('content')
<div class="row g-3">
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Langganan</h3></div>
<div class="list-group list-group-flush">
@forelse($webhooks as $w)
<div class="list-group-item">
<div class="d-flex justify-content-between align-items-center">
<div><div class="fw-bold">{{ $w->name }}</div><div class="text-secondary small">{{ $w->url }}</div>
<div class="mt-1">@foreach($w->events as $e)<span class="badge bg-blue-lt me-1">{{ $e }}</span>@endforeach</div>
<div class="text-secondary small mt-1">Secret: <code>{{ substr($w->secret, 0, 12) }}…</code> · terkirim: {{ $w->deliveries_count }}</div></div>
<div class="d-flex flex-column gap-1">
<form method="POST" action="{{ route('webhooks.toggle', $w) }}">@csrf<button class="btn btn-sm btn-white" type="submit">{{ $w->is_active ? 'Nonaktif' : 'Aktif' }}</button></form>
<form method="POST" action="{{ route('webhooks.rotate', $w) }}">@csrf<button class="btn btn-sm btn-white" type="submit">Rotate secret</button></form>
<form method="POST" action="{{ route('webhooks.destroy', $w) }}" onsubmit="return confirm('Hapus webhook?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit">Hapus</button></form>
</div>
</div>
</div>
@empty<div class="list-group-item text-secondary">Belum ada webhook.</div>@endforelse
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Webhook baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('webhooks.store') }}">@csrf
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required/></div>
<div class="mb-2"><label class="form-label">URL *</label><input name="url" type="url" class="form-control" required placeholder="https://…"/></div>
<div class="mb-2"><label class="form-label">Events *</label>
<div class="row g-1">@foreach($events as $e)<div class="col-md-6"><label class="form-check"><input type="checkbox" name="events[]" value="{{ $e }}" class="form-check-input" checked/><span class="form-check-label small">{{ $e }}</span></label></div>@endforeach</div></div>
<button class="btn btn-primary" type="submit">Simpan</button>
</form>
</div></div>
</div>
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Pengiriman terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Event</th><th>Percobaan</th><th>HTTP</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($recent as $d)
<tr>
<td class="text-secondary">{{ $d->created_at->format('d M H:i') }}</td>
<td><code>{{ $d->event }}</code></td>
<td class="text-end">{{ $d->attempts }}</td>
<td class="text-secondary">{{ $d->status_code ?? '—' }}</td>
<td>@if($d->status === 'DELIVERED')<span class="badge bg-green-lt">DELIVERED</span>@elseif($d->status === 'DEAD')<span class="badge bg-red-lt">DEAD</span>@else<span class="badge bg-yellow-lt">{{ $d->status }}</span>@endif</td>
<td class="text-end">@if($d->status !== 'DELIVERED')<form method="POST" action="{{ route('webhooks.retry', $d) }}">@csrf<button class="btn btn-sm btn-white" type="submit">Retry</button></form>@endif</td>
</tr>
@empty<tr><td colspan="6" class="text-center text-secondary py-3">Belum ada pengiriman.</td></tr>
@endforelse
</tbody></table></div></div>
</div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Automation Rules')
@section('subtitle', 'WHEN event → IF kondisi → THEN aksi (notifikasi peran / webhook)')
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nama</th><th>Event</th><th>Aksi</th><th>Terakhir fire</th><th>Aktif</th><th></th></tr></thead>
<tbody>
@forelse($rules as $r)
<tr>
<td>{{ $r->name }}<div class="text-secondary small">{{ $r->message }}</div></td>
<td><code>{{ $r->event }}</code></td>
<td class="text-secondary">{{ $r->action }} {{ $r->target_role ? '→ '.$r->target_role : '' }}</td>
<td class="text-secondary">{{ $r->last_fired_at?->format('d M H:i') ?? '—' }}</td>
<td>@if($r->is_active)<span class="badge bg-green-lt">ON</span>@else<span class="badge bg-secondary-lt">OFF</span>@endif</td>
<td class="text-end d-flex gap-1 justify-content-end">
<form method="POST" action="{{ route('automation.toggle', $r) }}">@csrf<button class="btn btn-sm btn-white" type="submit">Toggle</button></form>
<form method="POST" action="{{ route('automation.destroy', $r) }}" onsubmit="return confirm('Hapus rule?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form>
</td>
</tr>
@empty<tr><td colspan="6"><x-empty title="Belum ada rule"/></td></tr>
@endforelse
</tbody></table></div>
</div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Rule baru</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('automation.store') }}">@csrf
<div class="mb-2"><label class="form-label">Nama *</label><input name="name" class="form-control" required placeholder="cth. Stok kritis → gudang"/></div>
<div class="mb-2"><label class="form-label">Event *</label>
<select name="event" class="form-select">@foreach(\App\Models\AutomationRule::EVENTS as $e)<option value="{{ $e }}">{{ $e }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Aksi *</label>
<select name="action" class="form-select"><option value="notify_role">notify_role</option><option value="webhook">webhook</option></select></div>
<div class="mb-2"><label class="form-label">Peran target</label>
<select name="target_role" class="form-select"><option value="">—</option>@foreach(\Spatie\Permission\Models\Role::orderBy('name')->get() as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach</select></div>
<div class="mb-2"><label class="form-label">Pesan (mendukung @{{nama}} dsb.)</label><input name="message" class="form-control"/></div>
<button class="btn btn-primary" type="submit">Simpan rule</button>
</form>
</div></div>
</div>
</div>
@endsection

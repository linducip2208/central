@extends('layouts.app')
@section('title', 'Preferensi Notifikasi')
@section('content')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('notifications.preferences.save') }}">@csrf
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tipe</th><th class="text-center">In-app</th><th class="text-center">Email</th></tr></thead>
<tbody>
@forelse($types as $t)
@php $p = $prefs[$t] ?? null; @endphp
<tr><td><code>{{ $t }}</code></td>
<td class="text-center"><input type="checkbox" name="in_app[{{ $t }}]" value="1" class="form-check-input" @checked($p?->in_app ?? true)/></td>
<td class="text-center"><input type="checkbox" name="mail[{{ $t }}]" value="1" class="form-check-input" @checked($p?->mail ?? false)/></td></tr>
@empty<tr><td colspan="3" class="text-center text-secondary py-3">Belum ada tipe notifikasi.</td></tr>@endforelse
</tbody></table></div>
<button class="btn btn-primary mt-2" type="submit">Simpan preferensi</button>
</form>
</div></div>
@endsection

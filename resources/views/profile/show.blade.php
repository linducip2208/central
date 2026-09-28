@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Akun</h3></div>
<div class="card-body">
<dl class="row small">
<dt class="col-4">Nama</dt><dd class="col-8">{{ $user->name }}</dd>
<dt class="col-4">Email</dt><dd class="col-8">{{ $user->email }}</dd>
<dt class="col-4">Peran</dt><dd class="col-8">{{ $user->roles->pluck('name')->join(', ') }}</dd>
</dl>
<h4>Ganti password</h4>
<form method="POST" action="{{ route('profile.password') }}">@csrf
<div class="mb-2"><input name="current_password" type="password" class="form-control" placeholder="Password lama *" required/></div>
<div class="row g-2">
<div class="col-6"><input name="password" type="password" class="form-control" placeholder="Baru (min 8) *" required/></div>
<div class="col-6"><input name="password_confirmation" type="password" class="form-control" placeholder="Konfirmasi *" required/></div>
</div>
<button class="btn btn-white mt-2" type="submit">Simpan password</button>
</form>
</div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Autentikasi dua langkah (TOTP)</h3></div>
<div class="card-body">
@if($user->two_factor_confirmed_at)
<p><span class="badge bg-green-lt">AKTIF</span></p>
<form method="POST" action="{{ route('profile.2fa.disable') }}" onsubmit="return confirm('Nonaktifkan 2FA?')">@csrf<button class="btn btn-outline-danger" type="submit">Nonaktifkan 2FA</button></form>
@else
<p class="text-secondary">Pindai ke aplikasi authenticator (Google/Microsoft Authenticator):</p>
<div class="mb-2"><code style="word-break:break-all">{{ $provision }}</code></div>
<form method="POST" action="{{ route('profile.2fa.confirm') }}">@csrf
<div class="input-group"><input name="code" class="form-control" placeholder="Kode 6 digit *" maxlength="6" required/><button class="btn btn-primary" type="submit">Aktifkan</button></div>
</form>
@endif
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Token API pribadi</h3></div>
<div class="table-responsive"><table class="table table-sm table-vcenter card-table">
<thead><tr><th>Nama</th><th>Terakhir dipakai</th><th></th></tr></thead>
<tbody>
@forelse($tokens as $t)
<tr><td>{{ $t->name }}</td><td class="text-secondary">{{ $t->last_used_at ?? '—' }}</td>
<td class="text-end"><form method="POST" action="{{ route('profile.tokens.revoke', $t->id) }}" onsubmit="return confirm('Cabut token?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit">Cabut</button></form></td></tr>
@empty<tr><td colspan="3" class="text-center text-secondary py-2">Belum ada token.</td></tr>@endforelse
</tbody></table></div>
<div class="card-body border-top">
<form method="POST" action="{{ route('profile.tokens.create') }}">@csrf
<div class="input-group"><input name="name" class="form-control" placeholder="Nama perangkat *" required/><button class="btn btn-white" type="submit">Buat token</button></div>
</form>
</div></div>
</div>
</div>
@endsection

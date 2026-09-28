<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Verifikasi 2FA — {{ config('app.name') }}</title>
<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/css/tabler.min.css" rel="stylesheet"/>
</head>
<body class="d-flex flex-column">
<div class="page page-center">
<div class="container container-tight py-4">
<div class="card card-md"><div class="card-body">
<h2 class="h2 text-center mb-1">Verifikasi dua langkah</h2>
<p class="text-center text-secondary mb-4">Masukkan kode 6 digit dari aplikasi authenticator.</p>
@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif
<form method="POST" action="{{ route('2fa.verify') }}">@csrf
<div class="mb-3"><input name="code" class="form-control form-control-lg text-center" placeholder="••••••" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus/></div>
<button class="btn btn-primary w-100" type="submit">Verifikasi</button>
</form>
</div></div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js"></script>
</body>
</html>

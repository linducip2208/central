<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Masuk — {{ config('app.name') }}</title>
<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/css/tabler.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/@tabler/icons@3.28.1/tabler-icons.min.css" rel="stylesheet"/>
</head>
<body class="d-flex flex-column">
<div class="page page-center">
<div class="container container-tight py-4">
<div class="text-center mb-4">
<span class="avatar avatar-xl bg-green text-white mb-2"><i class="ti ti-chef-hat fs-2"></i></span>
<h1 class="h2">MBG Central Kitchen</h1>
<p class="text-secondary">Sistem operasional dapur Makan Bergizi Gratis</p>
</div>
<div class="card card-md">
<div class="card-body">
<h2 class="h2 text-center mb-4">Masuk ke akun Anda</h2>
@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif
<form method="POST" action="{{ route('login.attempt') }}" autocomplete="off">
@csrf
<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" placeholder="nama@mbg.id" value="{{ old('email') }}" required autofocus/>
</div>
<div class="mb-2">
<label class="form-label">Password</label>
<input type="password" name="password" class="form-control" placeholder="••••••••" required/>
</div>
<div class="mb-3">
<label class="form-check"><input type="checkbox" name="remember" class="form-check-input"/><span class="form-check-label">Ingat saya</span></label>
</div>
<div class="form-footer"><button type="submit" class="btn btn-primary w-100">Masuk</button></div>
</form>
</div>
</div>
<p class="text-center text-secondary mt-3">Demo: admin@mbg.id / password123</p>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
<meta name="theme-color" content="#2fb344"/>
<meta name="mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="default"/>
<link rel="manifest" href="/manifest.webmanifest"/>
<title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/css/tabler.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/@tabler/icons@3.28.1/tabler-icons.min.css" rel="stylesheet"/>
@stack('styles')
</head>
<body>
<div class="page">
@include('layouts.sidebar')
@include('layouts.navbar')
<div class="page-wrapper">
<div class="page-header d-print-none">
<div class="container-xl">
<div class="row g-2 align-items-center">
<div class="col">
<h2 class="page-title">@yield('title', 'Dashboard')</h2>
<div class="text-secondary mt-1">@yield('subtitle', '')</div>
</div>
<div class="col-auto ms-auto d-print-none">
@yield('actions')
</div>
</div>
</div>
</div>
<div class="page-body">
<div class="container-xl">
@if(session('success'))
<div class="alert alert-success alert-dismissible" role="alert">
<div class="d-flex"><div><i class="ti ti-check me-2"></i></div><div>{{ session('success') }}</div></div>
<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
<div class="d-flex"><div><i class="ti ti-alert-circle me-2"></i></div><div>{{ session('error') }}</div></div>
<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger" role="alert">
<div class="fw-bold mb-1">Periksa kembali isian formulir:</div>
<ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@yield('content')
</div>
</div>
<footer class="footer footer-transparent d-print-none">
<div class="container-xl"><div class="row text-center align-items-center flex-row-reverse">
<div class="col-12 col-lg-auto mt-3 mt-lg-0"><ul class="list-inline list-inline-dots mb-0"><li class="list-inline-item">{{ config('app.name') }} v{{ config('mbg.version') }}</li></ul></div>
</div></div>
</footer>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
if ('serviceWorker' in navigator && location.protocol.startsWith('http')) {
  navigator.serviceWorker.register('/sw.js').catch(() => {});
}
</script>
@stack('scripts')
</body>
</html>

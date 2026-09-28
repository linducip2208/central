<header class="navbar navbar-expand-md d-print-none">
<div class="container-xl">
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="navbar-nav flex-row order-md-last">
<div class="nav-item me-2">
<a href="{{ route('notifications.index') }}" class="nav-link px-2 position-relative" title="Notifikasi">
<i class="ti ti-bell"></i>
@php $unread = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp
@if($unread > 0)<span class="badge bg-red position-absolute top-0 start-100 translate-middle">{{ $unread > 9 ? '9+' : $unread }}</span>@endif
</a>
</div>
<div class="nav-item dropdown">
<a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Menu pengguna">
<span class="avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
<div class="d-none d-xl-block ps-2">
<div>{{ auth()->user()->name ?? '-' }}</div>
<div class="mt-1 small text-secondary">{{ auth()->user()?->roles->pluck('name')->join(', ') ?? '-' }}</div>
</div>
</a>
<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
<a href="{{ route('profile.show') }}" class="dropdown-item">Profil & keamanan</a>
<a href="{{ route('notifications.index') }}" class="dropdown-item">Notifikasi</a>
<div class="dropdown-divider"></div>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit">Keluar</button></form>
</div>
</div>
</div>
<div class="collapse navbar-collapse" id="navbar-menu">
<form class="d-none d-md-flex" method="GET" action="{{ route('search.index') }}">
<div class="input-icon">
<span class="input-icon-addon"><i class="ti ti-search"></i></span>
<input type="text" class="form-control" placeholder="Cari global…" name="q" value="{{ request('q') }}"/>
</div>
</form>
</div>
</div>
</header>

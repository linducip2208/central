<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
<div class="container-fluid">
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<h1 class="navbar-brand navbar-brand-autodark">
<a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
<span class="avatar avatar-sm bg-green text-white"><i class="ti ti-chef-hat"></i></span>
<span class="fw-bold">MBG Kitchen</span>
</a>
</h1>
<div class="collapse navbar-collapse" id="sidebar-menu">
<ul class="navbar-nav pt-lg-3">
@foreach($mbgMenu ?? [] as $item)
@if(isset($item['section']))
<li class="nav-item mt-2"><span class="nav-link text-secondary text-uppercase small fw-bold">{{ $item['section'] }}</span></li>
@elseif(isset($item['children']))
@php $canSee = collect($item['children'])->contains(fn($c) => empty($c['perm']) || auth()->user()?->can($c['perm']) || auth()->user()?->hasRole(['super-admin','admin'])); @endphp
@if($canSee)
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#menu-{{ md5($item['label']) }}" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
<span class="nav-link-icon d-md-none d-lg-inline-block"><i class="{{ $item['icon'] ?? 'ti ti-circle' }}"></i></span>
<span class="nav-link-title">{{ $item['label'] }}</span>
</a>
<div class="dropdown-menu">
@foreach($item['children'] as $child)
@if(empty($child['perm']) || auth()->user()?->can($child['perm']) || auth()->user()?->hasRole(['super-admin','admin']))
<a class="dropdown-item {{ request()->routeIs(str_replace('.index','.',$child['route'] ?? '').'*') ? 'active' : '' }}" href="{{ isset($child['route']) ? route($child['route']) : '#' }}">{{ $child['label'] }}</a>
@endif
@endforeach
</div>
</li>
@endif
@else
@if(empty($item['perm']) || auth()->user()?->can($item['perm']) || auth()->user()?->hasRole(['super-admin','admin']))
<li class="nav-item">
<a class="nav-link {{ isset($item['route']) && request()->routeIs($item['route'].'*') ? 'active' : '' }}" href="{{ isset($item['route']) ? route($item['route']) : '#' }}">
<span class="nav-link-icon d-md-none d-lg-inline-block"><i class="{{ $item['icon'] ?? 'ti ti-circle' }}"></i></span>
<span class="nav-link-title">{{ $item['label'] }}</span>
</a>
</li>
@endif
@endif
@endforeach
</ul>
</div>
</div>
</aside>

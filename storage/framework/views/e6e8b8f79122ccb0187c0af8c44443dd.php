<header class="navbar navbar-expand-md d-print-none">
<div class="container-xl">
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="navbar-nav flex-row order-md-last">
<div class="nav-item dropdown">
<a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Menu pengguna">
<span class="avatar avatar-sm"><?php echo e(strtoupper(substr(auth()->user()->name ?? 'U', 0, 1))); ?></span>
<div class="d-none d-xl-block ps-2">
<div><?php echo e(auth()->user()->name ?? '-'); ?></div>
<div class="mt-1 small text-secondary"><?php echo e(auth()->user()?->roles->pluck('name')->join(', ') ?? '-'); ?></div>
</div>
</a>
<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
<a href="<?php echo e(route('notifications.index')); ?>" class="dropdown-item">Notifikasi</a>
<div class="dropdown-divider"></div>
<form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="dropdown-item" type="submit">Keluar</button></form>
</div>
</div>
</div>
<div class="collapse navbar-collapse" id="navbar-menu">
<form class="d-none d-md-flex" method="GET" action="<?php echo e(route('reports.index')); ?>">
<div class="input-icon">
<span class="input-icon-addon"><i class="ti ti-search"></i></span>
<input type="text" class="form-control" placeholder="Cari…" name="q" value="<?php echo e(request('q')); ?>"/>
</div>
</form>
</div>
</div>
</header>
<?php /**PATH resources/views\layouts\navbar.blade.php ENDPATH**/ ?>
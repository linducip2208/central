<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
<div class="container-fluid">
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<h1 class="navbar-brand navbar-brand-autodark">
<a href="<?php echo e(route('dashboard')); ?>" class="d-flex align-items-center gap-2 text-decoration-none">
<span class="avatar avatar-sm bg-green text-white"><i class="ti ti-chef-hat"></i></span>
<span class="fw-bold">MBG Kitchen</span>
</a>
</h1>
<div class="collapse navbar-collapse" id="sidebar-menu">
<ul class="navbar-nav pt-lg-3">
<?php $__currentLoopData = $mbgMenu ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if(isset($item['section'])): ?>
<li class="nav-item mt-2"><span class="nav-link text-secondary text-uppercase small fw-bold"><?php echo e($item['section']); ?></span></li>
<?php elseif(isset($item['children'])): ?>
<?php $canSee = collect($item['children'])->contains(fn($c) => empty($c['perm']) || auth()->user()?->can($c['perm']) || auth()->user()?->hasRole(['super-admin','admin'])); ?>
<?php if($canSee): ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#menu-<?php echo e(md5($item['label'])); ?>" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
<span class="nav-link-icon d-md-none d-lg-inline-block"><i class="<?php echo e($item['icon'] ?? 'ti ti-circle'); ?>"></i></span>
<span class="nav-link-title"><?php echo e($item['label']); ?></span>
</a>
<div class="dropdown-menu">
<?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if(empty($child['perm']) || auth()->user()?->can($child['perm']) || auth()->user()?->hasRole(['super-admin','admin'])): ?>
<a class="dropdown-item <?php echo e(request()->routeIs(str_replace('.index','.',$child['route'] ?? '').'*') ? 'active' : ''); ?>" href="<?php echo e(isset($child['route']) ? route($child['route']) : '#'); ?>"><?php echo e($child['label']); ?></a>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
</li>
<?php endif; ?>
<?php else: ?>
<?php if(empty($item['perm']) || auth()->user()?->can($item['perm']) || auth()->user()?->hasRole(['super-admin','admin'])): ?>
<li class="nav-item">
<a class="nav-link <?php echo e(isset($item['route']) && request()->routeIs($item['route'].'*') ? 'active' : ''); ?>" href="<?php echo e(isset($item['route']) ? route($item['route']) : '#'); ?>">
<span class="nav-link-icon d-md-none d-lg-inline-block"><i class="<?php echo e($item['icon'] ?? 'ti ti-circle'); ?>"></i></span>
<span class="nav-link-title"><?php echo e($item['label']); ?></span>
</a>
</li>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
</div>
</div>
</aside>
<?php /**PATH D:\project laravel\centralkitchen\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> — <?php echo e(config('app.name')); ?></title>
<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/css/tabler.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/@tabler/icons@3.28.1/tabler-icons.min.css" rel="stylesheet"/>
<?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
<div class="page">
<?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('layouts.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="page-wrapper">
<div class="page-header d-print-none">
<div class="container-xl">
<div class="row g-2 align-items-center">
<div class="col">
<h2 class="page-title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h2>
<div class="text-secondary mt-1"><?php echo $__env->yieldContent('subtitle', ''); ?></div>
</div>
<div class="col-auto ms-auto d-print-none">
<?php echo $__env->yieldContent('actions'); ?>
</div>
</div>
</div>
</div>
<div class="page-body">
<div class="container-xl">
<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible" role="alert">
<div class="d-flex"><div><i class="ti ti-check me-2"></i></div><div><?php echo e(session('success')); ?></div></div>
<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="alert alert-danger alert-dismissible" role="alert">
<div class="d-flex"><div><i class="ti ti-alert-circle me-2"></i></div><div><?php echo e(session('error')); ?></div></div>
<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
<?php endif; ?>
<?php if($errors->any()): ?>
<div class="alert alert-danger" role="alert">
<div class="fw-bold mb-1">Periksa kembali isian formulir:</div>
<ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php echo $__env->yieldContent('content'); ?>
</div>
</div>
<footer class="footer footer-transparent d-print-none">
<div class="container-xl"><div class="row text-center align-items-center flex-row-reverse">
<div class="col-12 col-lg-auto mt-3 mt-lg-0"><ul class="list-inline list-inline-dots mb-0"><li class="list-inline-item"><?php echo e(config('app.name')); ?> v<?php echo e(config('mbg.version')); ?></li></ul></div>
</div></div>
</footer>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH resources/views\layouts\app.blade.php ENDPATH**/ ?>
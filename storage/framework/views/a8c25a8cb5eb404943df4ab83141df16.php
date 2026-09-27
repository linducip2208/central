<?php $__env->startSection('title', 'Notifikasi'); ?>
<?php $__env->startSection('actions'); ?>
<form method="POST" action="<?php echo e(route('notifications.read-all')); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-white" type="submit">Tandai semua dibaca</button></form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="list-group list-group-flush">
<?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div class="list-group-item <?php echo e($n->read_at ? '' : 'bg-blue-lt'); ?>">
<div class="d-flex justify-content-between align-items-center">
<div><span class="badge bg-blue-lt me-2"><?php echo e($n->data['type'] ?? '-'); ?></span><span class="small"><?php echo e($n->created_at->diffForHumans()); ?></span>
<div class="text-secondary small"><?php echo e(json_encode($n->data['data'] ?? [])); ?></div></div>
<?php if(!$n->read_at): ?>
<form method="POST" action="<?php echo e(route('notifications.read', $n->id)); ?>"><?php echo csrf_field(); ?><button class="btn btn-sm btn-white" type="submit">Tandai dibaca</button></form>
<?php endif; ?>
</div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="list-group-item text-secondary">Tidak ada notifikasi.</div><?php endif; ?>
</div></div>
<div class="mt-3"><?php echo e($notifications->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/notifications/index.blade.php ENDPATH**/ ?>
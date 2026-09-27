<?php $__env->startSection('title', 'Peran & Izin'); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title"><?php echo e($role->name); ?> <span class="badge bg-blue-lt ms-1"><?php echo e($role->permissions->count()); ?> izin</span></h3></div>
<div class="card-body">
<form method="POST" action="<?php echo e(route('roles.permissions', $role)); ?>"><?php echo csrf_field(); ?>
<?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="mb-2"><div class="fw-bold small text-secondary text-uppercase"><?php echo e($group); ?></div>
<div class="row g-1">
<?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="col-md-6"><label class="form-check"><input type="checkbox" name="permissions[]" value="<?php echo e($p->name); ?>" class="form-check-input" <?php if($role->permissions->contains('name', $p->name)): echo 'checked'; endif; ?>/><span class="form-check-label small"><?php echo e($p->name); ?></span></label></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<button class="btn btn-primary btn-sm" type="submit">Simpan izin</button>
</form>
</div></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\admin\roles.blade.php ENDPATH**/ ?>
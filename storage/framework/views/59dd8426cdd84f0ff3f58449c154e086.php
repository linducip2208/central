<?php $__env->startSection('title', 'Pengaturan'); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Nilai tersimpan</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Key</th><th>Value</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $all; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><code><?php echo e($k); ?></code></td><td class="text-secondary small"><?php echo e(is_string($v) ? $v : json_encode($v)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="2" class="text-center text-secondary py-3">Belum ada pengaturan.</td></tr>
<?php endif; ?>
</tbody></table></div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah / ubah</h3></div>
<div class="card-body">
<form method="POST" action="<?php echo e(route('settings.store')); ?>"><?php echo csrf_field(); ?>
<div class="mb-2"><label class="form-label">Key *</label><input name="key" class="form-control" required placeholder="cth. app.tagline"/></div>
<div class="mb-2"><label class="form-label">Value *</label><textarea name="value" class="form-control" rows="3" required></textarea></div>
<button class="btn btn-primary" type="submit">Simpan</button>
</form>
</div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/admin/settings.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Buat Rencana Produksi'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('production-plans.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required><?php $__currentLoopData = $kitchens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k->id); ?>"><?php echo e($k->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Menu (item otomatis dipecah)</label>
<select name="menu_id" class="form-select"><option value="">— tanpa menu —</option><?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($m->id); ?>"><?php echo e($m->name); ?> · <?php echo e($m->menu_date); ?> (<?php echo e($m->items->count()); ?> produk)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><label class="form-label">Tanggal *</label><input name="plan_date" type="date" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required/></div>
<div class="col-md-2"><label class="form-label">Target porsi *</label><input name="target_portions" type="number" min="1" class="form-control" required/></div>
<div class="col-md-12"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan</button></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/production-plans/form.blade.php ENDPATH**/ ?>
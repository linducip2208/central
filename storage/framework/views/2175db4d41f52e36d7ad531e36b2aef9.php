<?php $__env->startSection('title', 'Buat Stock Opname'); ?>
<?php $__env->startSection('subtitle', 'Snapshot stok sistem per batch otomatis dibuat saat opname disimpan'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('stock-opnames.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Tanggal *</label><input name="opname_date" type="date" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required/></div>
<div class="col-md-4"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Buat + snapshot</button></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\stock-opnames\form.blade.php ENDPATH**/ ?>
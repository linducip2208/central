<?php $__env->startSection('title', 'Penyesuaian Stok'); ?>
<?php $__env->startSection('subtitle', 'Selisih dicatat sebagai movement ADJUSTMENT — wajib diisi alasan'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('inventory.adjust')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Bahan *</label>
<select name="ingredient_id" class="form-select" required><?php $__currentLoopData = $ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Qty baru (sistem saat ini → diganti) *</label><input name="new_qty" type="number" step="0.001" min="0" class="form-control" required/></div>
<div class="col-md-12"><label class="form-label">Alasan (min. 5 karakter) *</label><textarea name="notes" class="form-control" rows="2" required></textarea></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-warning" type="submit" onclick="return confirm('Posting penyesuaian ke ledger?')">Posting adjustment</button></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\inventory\adjust.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Catat Waste'); ?>
<?php $__env->startSection('subtitle', 'Stok berkurang via ledger (FEFO) + nilai kerugian dihitung dari harga batch'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('wastes.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Bahan *</label>
<select name="ingredient_id" class="form-select" required><?php $__currentLoopData = $ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?> (<?php echo e($i->unit->symbol ?? ''); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><label class="form-label">Qty *</label><input name="qty" type="number" step="0.001" min="0.001" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Alasan *</label>
<select name="reason" class="form-select"><?php $__currentLoopData = ['EXPIRED','SPOILED','OVER_PRODUCTION','QC_REJECT','OTHER']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r); ?>"><?php echo e($r); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-6"><label class="form-label">Metode pembuangan</label><input name="disposal_method" class="form-control" placeholder="cth. kompos, bank sampah"/></div>
<div class="col-md-6"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<div class="form-footer mt-3"><button class="btn btn-danger" type="submit" onclick="return confirm('Catat waste dan kurangi stok?')">Catat waste</button></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/wastes/form.blade.php ENDPATH**/ ?>
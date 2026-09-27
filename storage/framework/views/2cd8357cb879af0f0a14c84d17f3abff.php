<?php $__env->startSection('title', 'Buat Purchase Request'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('purchase-requests.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required><?php $__currentLoopData = $kitchens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k->id); ?>"><?php echo e($k->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Gudang</label>
<select name="warehouse_id" class="form-select"><option value="">—</option><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Dibutuhkan tanggal</label><input name="needed_date" type="date" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<h4 class="mt-4">Item *</h4>
<div id="pr-items" class="row g-2">
<div class="col-md-8"><select name="items[0][ingredient_id]" class="form-select" required><option value="">— bahan —</option><?php $__currentLoopData = $ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?> (<?php echo e($i->unit->symbol ?? ''); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><input name="items[0][qty]" type="number" step="0.001" min="0.001" class="form-control" placeholder="qty" required/></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addPr()">+ Tambah item</button></div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan draft</button><a href="<?php echo e(route('purchase-requests.index')); ?>" class="btn btn-white">Batal</a></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
let pi = 1;
function addPr() {
document.getElementById('pr-items').insertAdjacentHTML('beforeend', `<div class="col-md-8"><select name="items[${pi}][ingredient_id]" class="form-select"><?php $__currentLoopData = $ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-4"><input name="items[${pi}][qty]" type="number" step="0.001" min="0.001" class="form-control" placeholder="qty"/></div>`);
pi++;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/purchase-requests/form.blade.php ENDPATH**/ ?>
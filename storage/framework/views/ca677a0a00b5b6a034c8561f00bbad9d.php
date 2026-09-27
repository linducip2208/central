<?php $__env->startSection('title', 'Buat Distribusi'); ?>
<?php $__env->startSection('subtitle', 'Delivery per sekolah otomatis dibuat dari alokasi di bawah'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('distributions.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Central kitchen *</label>
<select name="central_kitchen_id" class="form-select" required><?php $__currentLoopData = $kitchens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k->id); ?>"><?php echo e($k->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Dari packaging</label>
<select name="packaging_id" class="form-select"><option value="">—</option><?php $__currentLoopData = $packagings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->number); ?> (<?php echo e(number_format($p->packages_done)); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><label class="form-label">Kendaraan</label><input name="vehicle_no" class="form-control" placeholder="B 1234 XX"/></div>
<div class="col-md-2"><label class="form-label">Sopir</label><input name="driver_name" class="form-control"/></div>
</div>
<h4 class="mt-4">Alokasi sekolah *</h4>
<div id="dist-items" class="row g-2">
<div class="col-md-5"><select name="items[0][school_id]" class="form-select" required><option value="">— sekolah —</option><?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?> (<?php echo e(number_format($s->target_portions)); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><select name="items[0][product_id]" class="form-select" required><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><input name="items[0][qty]" type="number" min="1" class="form-control" placeholder="porsi" required/></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addDist()">+ Tambah sekolah</button></div>
<div class="form-footer mt-3"><button class="btn btn-primary" type="submit">Simpan + buat delivery</button></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
let di = 1;
function addDist() {
document.getElementById('dist-items').insertAdjacentHTML('beforeend', `<div class="col-md-5"><select name="items[${di}][school_id]" class="form-select"><?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-4"><select name="items[${di}][product_id]" class="form-select"><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-3"><input name="items[${di}][qty]" type="number" min="1" class="form-control" placeholder="porsi"/></div>`);
di++;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/distributions/form.blade.php ENDPATH**/ ?>
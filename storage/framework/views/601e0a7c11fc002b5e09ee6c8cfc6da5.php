<?php $__env->startSection('title', 'Buat Resep'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('recipes.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Produk *</label>
<select name="product_id" class="form-select" required><option value="">—</option><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Nama resep *</label><input name="name" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Yield (hasil) *</label><input name="yield_qty" type="number" step="0.001" min="0.01" value="1" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Waktu masak (mnt)</label><input name="cook_time_minutes" type="number" min="0" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Instruksi</label><textarea name="instructions" class="form-control" rows="2"></textarea></div>
</div>
<h4 class="mt-4">Bahan *</h4>
<div id="recipe-items" class="row g-2">
<div class="col-md-5"><select name="items[0][ingredient_id]" class="form-select" required><option value="">— bahan —</option><?php $__currentLoopData = $ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?> (<?php echo e($i->unit->symbol ?? ''); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><input name="items[0][qty]" type="number" step="0.0001" min="0.0001" class="form-control" placeholder="qty" required/></div>
<div class="col-md-3"><select name="items[0][unit_id]" class="form-select"><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($u->id); ?>"><?php echo e($u->symbol); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><div class="input-group"><input name="items[0][waste_factor_pct]" type="number" step="0.01" min="0" max="100" value="0" class="form-control"/><span class="input-group-text">% susut</span></div></div>
</div>
<div class="mt-2"><button type="button" class="btn btn-white btn-sm" onclick="addIng()">+ Tambah bahan</button></div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan resep</button><a href="<?php echo e(route('recipes.index')); ?>" class="btn btn-white">Batal</a></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
let ri = 1;
function addIng() {
const wrap = document.getElementById('recipe-items');
wrap.insertAdjacentHTML('beforeend', `<div class="col-md-5"><select name="items[${ri}][ingredient_id]" class="form-select"><?php $__currentLoopData = $ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-2"><input name="items[${ri}][qty]" type="number" step="0.0001" min="0.0001" class="form-control" placeholder="qty"/></div><div class="col-md-3"><select name="items[${ri}][unit_id]" class="form-select"><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($u->id); ?>"><?php echo e($u->symbol); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="col-md-2"><input name="items[${ri}][waste_factor_pct]" type="number" value="0" class="form-control"/></div>`);
ri++;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/recipes/form.blade.php ENDPATH**/ ?>
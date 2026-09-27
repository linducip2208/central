<?php $__env->startSection('title', ($ingredient->exists ? 'Ubah' : 'Tambah') . ' Bahan Baku'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e($ingredient->exists ? route('ingredients.update', $ingredient) : route('ingredients.store')); ?>">
<?php echo csrf_field(); ?> <?php if($ingredient->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama bahan *</label><input name="name" class="form-control" value="<?php echo e(old('name', $ingredient->name)); ?>" required/></div>
<div class="col-md-3"><label class="form-label">Kategori *</label>
<select name="category" class="form-select"><?php $__currentLoopData = ['STAPLE','PROTEIN','VEGETABLE','FRUIT','SPICE','OIL','OTHER']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>" <?php if(old('category', $ingredient->category) === $c): echo 'selected'; endif; ?>><?php echo e($c); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><label class="form-label">Satuan dasar *</label>
<select name="unit_id" class="form-select"><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($u->id); ?>" <?php if(old('unit_id', $ingredient->unit_id) == $u->id): echo 'selected'; endif; ?>><?php echo e($u->name); ?> (<?php echo e($u->symbol); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><label class="form-label">Harga standar *</label><input name="standard_price" type="number" min="0" step="0.01" class="form-control" value="<?php echo e(old('standard_price', $ingredient->standard_price ?? 0)); ?>" required/></div>
<div class="col-md-3"><label class="form-label">Stok minimum</label><input name="min_stock" type="number" min="0" step="0.001" class="form-control" value="<?php echo e(old('min_stock', $ingredient->min_stock ?? 0)); ?>"/></div>
<div class="col-md-3"><label class="form-label">Stok maksimum</label><input name="max_stock" type="number" min="0" step="0.001" class="form-control" value="<?php echo e(old('max_stock', $ingredient->max_stock ?? 0)); ?>"/></div>
<div class="col-md-3"><label class="form-label">Daya simpan (hari)</label><input name="shelf_life_days" type="number" min="0" class="form-control" value="<?php echo e(old('shelf_life_days', $ingredient->shelf_life_days ?? 0)); ?>"/></div>
<div class="col-md-12"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" <?php if(old('is_active', $ingredient->is_active ?? true)): echo 'checked'; endif; ?>/><span class="form-check-label">Aktif</span></label></div>
</div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan</button><a href="<?php echo e(route('ingredients.index')); ?>" class="btn btn-white">Batal</a></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/ingredients/form.blade.php ENDPATH**/ ?>
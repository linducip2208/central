<?php $__env->startSection('title', ($product->exists ? 'Ubah' : 'Tambah') . ' Produk'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e($product->exists ? route('products.update', $product) : route('products.store')); ?>">
<?php echo csrf_field(); ?> <?php if($product->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama produk *</label><input name="name" class="form-control" value="<?php echo e(old('name', $product->name)); ?>" required/></div>
<div class="col-md-3"><label class="form-label">Kategori *</label>
<select name="category" class="form-select"><?php $__currentLoopData = ['MEAL','SNACK','DRINK','EXTRA']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>" <?php if(old('category', $product->category) === $c): echo 'selected'; endif; ?>><?php echo e($c); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><label class="form-label">Satuan *</label>
<select name="unit_id" class="form-select"><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($u->id); ?>" <?php if(old('unit_id', $product->unit_id) == $u->id): echo 'selected'; endif; ?>><?php echo e($u->name); ?> (<?php echo e($u->symbol); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><label class="form-label">Ukuran porsi (gram)</label><input name="portion_size_gram" type="number" min="0" class="form-control" value="<?php echo e(old('portion_size_gram', $product->portion_size_gram ?? 0)); ?>"/></div>
<div class="col-md-9"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="2"><?php echo e(old('description', $product->description)); ?></textarea></div>
<div class="col-md-12"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" <?php if(old('is_active', $product->is_active ?? true)): echo 'checked'; endif; ?>/><span class="form-check-label">Aktif</span></label></div>
</div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan</button><a href="<?php echo e(route('products.index')); ?>" class="btn btn-white">Batal</a></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/products/form.blade.php ENDPATH**/ ?>
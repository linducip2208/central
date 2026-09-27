<?php $__env->startSection('title', ($supplier->exists ? 'Ubah' : 'Tambah') . ' Supplier'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e($supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store')); ?>">
<?php echo csrf_field(); ?> <?php if($supplier->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama supplier *</label><input name="name" class="form-control" value="<?php echo e(old('name', $supplier->name)); ?>" required/></div>
<div class="col-md-3"><label class="form-label">Kategori *</label>
<select name="category" class="form-select"><?php $__currentLoopData = ['FOOD','NON_FOOD','SERVICE']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>" <?php if(old('category', $supplier->category) === $c): echo 'selected'; endif; ?>><?php echo e($c); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><label class="form-label">Status *</label>
<select name="status" class="form-select"><?php $__currentLoopData = ['ACTIVE','INACTIVE']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s); ?>" <?php if(old('status', $supplier->status ?? 'ACTIVE') === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Kontak person</label><input name="contact_person" class="form-control" value="<?php echo e(old('contact_person', $supplier->contact_person)); ?>"/></div>
<div class="col-md-4"><label class="form-label">Telepon</label><input name="phone" class="form-control" value="<?php echo e(old('phone', $supplier->phone)); ?>"/></div>
<div class="col-md-4"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?php echo e(old('email', $supplier->email)); ?>"/></div>
<div class="col-md-8"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2"><?php echo e(old('address', $supplier->address)); ?></textarea></div>
<div class="col-md-4"><label class="form-label">NPWP</label><input name="tax_number" class="form-control" value="<?php echo e(old('tax_number', $supplier->tax_number)); ?>"/></div>
<?php if($supplier->exists): ?>
<div class="col-md-2"><label class="form-label">Rating (0–5)</label><input name="rating" type="number" min="0" max="5" class="form-control" value="<?php echo e(old('rating', $supplier->rating)); ?>"/></div>
<?php endif; ?>
</div>
<div class="form-footer mt-3 d-flex gap-2">
<button class="btn btn-primary" type="submit">Simpan</button>
<a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-white">Batal</a>
</div>
</form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/suppliers/form.blade.php ENDPATH**/ ?>
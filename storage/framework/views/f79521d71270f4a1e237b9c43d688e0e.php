<?php $__env->startSection('title', $supplier->name); ?>
<?php $__env->startSection('subtitle', $supplier->code . ' · ' . $supplier->category); ?>
<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('suppliers.edit', $supplier)); ?>" class="btn btn-white">Ubah</a>
<a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-ghost-secondary">Kembali</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<div class="mb-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $supplier->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($supplier->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></div>
<dl class="row">
<dt class="col-5">Kontak</dt><dd class="col-7"><?php echo e($supplier->contact_person ?? '-'); ?></dd>
<dt class="col-5">Telepon</dt><dd class="col-7"><?php echo e($supplier->phone ?? '-'); ?></dd>
<dt class="col-5">Email</dt><dd class="col-7"><?php echo e($supplier->email ?? '-'); ?></dd>
<dt class="col-5">NPWP</dt><dd class="col-7"><?php echo e($supplier->tax_number ?? '-'); ?></dd>
<dt class="col-5">Rating</dt><dd class="col-7"><?php echo e($supplier->rating); ?>/5</dd>
<dt class="col-5">Alamat</dt><dd class="col-7"><?php echo e($supplier->address ?? '-'); ?></dd>
</dl>
<form method="POST" action="<?php echo e(route('suppliers.destroy', $supplier)); ?>" onsubmit="return confirm('Hapus supplier ini?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Purchase order terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th class="text-end">Total</th><th>Status</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $supplier->purchaseOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><a href="<?php echo e(route('purchase-orders.show', $po)); ?>"><?php echo e($po->number); ?></a></td><td class="text-secondary"><?php echo e($po->order_date); ?></td><td class="text-end"><?php echo e(mbg_currency($po->grand_total)); ?></td><td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $po->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($po->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4" class="text-center text-secondary py-3">Belum ada PO.</td></tr><?php endif; ?>
</tbody></table></div>
</div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/suppliers/show.blade.php ENDPATH**/ ?>
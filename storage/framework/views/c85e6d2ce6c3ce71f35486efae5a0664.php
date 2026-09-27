<?php $__env->startSection('title', 'Packaging ' . $pkg->number); ?>
<?php $__env->startSection('actions'); ?>
<?php if($pkg->status === 'DRAFT'): ?>
<form method="POST" action="<?php echo e(route('packagings.complete', $pkg)); ?>" class="d-inline"><?php echo csrf_field(); ?>
<div class="input-group"><input name="packages_done" type="number" min="1" max="<?php echo e($pkg->packages_planned); ?>" value="<?php echo e($pkg->packages_planned); ?>" class="form-control" required/><button class="btn btn-success" type="submit">Selesaikan</button></div>
</form>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<dl class="row small">
<dt class="col-3">Status</dt><dd class="col-9"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $pkg->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pkg->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></dd>
<dt class="col-3">Dari WO</dt><dd class="col-9"><?php echo e($pkg->productionOrder->number ?? '-'); ?></dd>
<dt class="col-3">Rencana / selesai</dt><dd class="col-9"><?php echo e(number_format($pkg->packages_planned)); ?> / <?php echo e(number_format($pkg->packages_done)); ?> <?php echo e($pkg->package_type); ?></dd>
</dl>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Produk</th><th class="text-end">Terkemas</th></tr></thead>
<tbody>
<?php $__currentLoopData = $pkg->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td><?php echo e($it->product->name ?? '-'); ?></td><td class="text-end"><?php echo e(number_format($it->qty_packed)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/packagings/show.blade.php ENDPATH**/ ?>
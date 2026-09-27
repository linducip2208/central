<?php $__env->startSection('title', 'Laporan Produksi'); ?>
<?php $__env->startSection('subtitle', $from . ' s.d. ' . $to); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="from" type="date" class="form-control" value="<?php echo e($from); ?>"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="<?php echo e($to); ?>"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>WO</th><th>Produk</th><th class="text-end">Rencana</th><th class="text-end">Hasil</th><th class="text-end">Reject</th><th>Status</th></tr></thead>
<tbody>
<?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td class="text-secondary"><?php echo e($o->production_date); ?></td><td><?php echo e($o->number); ?></td><td class="text-secondary"><?php echo e($o->product->name ?? ''); ?></td><td class="text-end"><?php echo e(number_format($o->planned_qty, 0)); ?></td><td class="text-end"><?php echo e(number_format($o->produced_qty, 0)); ?></td><td class="text-end"><?php echo e(number_format($o->rejected_qty, 0)); ?></td><td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $o->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($o->status)]); ?>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<tr class="fw-bold"><td colspan="3" class="text-end">Total hasil</td><td class="text-end" colspan="4"><?php echo e(number_format($orders->sum('produced_qty'), 0)); ?> porsi</td></tr>
</tbody></table></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\reports\production.blade.php ENDPATH**/ ?>
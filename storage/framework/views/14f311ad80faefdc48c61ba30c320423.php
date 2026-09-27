<?php $__env->startSection('title', 'Costing ' . $costing->costing_date); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<dl class="row">
<dt class="col-4">WO</dt><dd class="col-8"><?php echo e($costing->productionOrder->number ?? '-'); ?> (<?php echo e($costing->productionOrder->product->name ?? ''); ?>)</dd>
<dt class="col-4">Menu</dt><dd class="col-8"><?php echo e($costing->menu->name ?? '-'); ?></dd>
<dt class="col-4">Material (aktual ledger)</dt><dd class="col-8"><?php echo e(mbg_currency($costing->material_cost)); ?></dd>
<dt class="col-4">Tenaga</dt><dd class="col-8"><?php echo e(mbg_currency($costing->labor_cost)); ?></dd>
<dt class="col-4">Overhead</dt><dd class="col-8"><?php echo e(mbg_currency($costing->overhead_cost)); ?></dd>
<dt class="col-4">Kemasan</dt><dd class="col-8"><?php echo e(mbg_currency($costing->packaging_cost)); ?></dd>
<dt class="col-4">Distribusi</dt><dd class="col-8"><?php echo e(mbg_currency($costing->delivery_cost)); ?></dd>
<dt class="col-4 fw-bold">Total</dt><dd class="col-8 fw-bold"><?php echo e(mbg_currency($costing->total_cost)); ?></dd>
<dt class="col-4">Porsi</dt><dd class="col-8"><?php echo e(number_format($costing->portions)); ?></dd>
<dt class="col-4 fw-bold">Per porsi</dt><dd class="col-8 fw-bold text-green"><?php echo e(mbg_currency($costing->cost_per_portion)); ?></dd>
</dl>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/costings/show.blade.php ENDPATH**/ ?>
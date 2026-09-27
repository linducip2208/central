<?php $__env->startSection('title', 'Laporan Stok'); ?>
<?php $__env->startSection('subtitle', 'Total nilai persediaan: ' . mbg_currency($totalValue)); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><select name="warehouse_id" class="form-select" onchange="this.form.submit()"><option value="">— Semua gudang —</option><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>" <?php if(request('warehouse_id') == $w->id): echo 'selected'; endif; ?>><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit" onclick="window.print()">Cetak</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Item</th><th>Batch</th><th class="text-end">Qty</th><th class="text-end">Avg cost</th><th class="text-end">Nilai</th></tr></thead>
<tbody>
<?php $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td class="text-secondary"><?php echo e($s->warehouse->name ?? ''); ?></td><td><?php echo e($s->item_name ?? ''); ?></td><td class="text-secondary"><?php echo e($s->batch->batch_no ?? '-'); ?></td><td class="text-end"><?php echo e(number_format($s->qty, 2)); ?></td><td class="text-end"><?php echo e(mbg_currency($s->avg_cost)); ?></td><td class="text-end"><?php echo e(mbg_currency($s->stock_value)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<tr class="fw-bold"><td colspan="5" class="text-end">Total</td><td class="text-end"><?php echo e(mbg_currency($totalValue)); ?></td></tr>
</tbody></table></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/reports/stock.blade.php ENDPATH**/ ?>
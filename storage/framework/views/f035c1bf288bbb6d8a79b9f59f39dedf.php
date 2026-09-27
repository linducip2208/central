<?php $__env->startSection('title', 'Laporan Keuangan'); ?>
<?php $__env->startSection('subtitle', $from . ' s.d. ' . $to); ?>
<?php $__env->startSection('content'); ?>
<div class="row row-deck row-cards mb-3">
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Belanja (PO disetujui)</div><div class="h2"><?php echo e(mbg_currency($purchases)); ?></div></div></div></div>
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Biaya produksi</div><div class="h2"><?php echo e(mbg_currency($costings->sum('total_cost'))); ?></div></div></div></div>
<div class="col-sm-4"><div class="card"><div class="card-body"><div class="text-secondary">Rugi waste</div><div class="h2 text-red"><?php echo e(mbg_currency($wasteLoss)); ?></div></div></div></div>
</div>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="from" type="date" class="form-control" value="<?php echo e($from); ?>"/></div>
<div class="col-md-3"><input name="to" type="date" class="form-control" value="<?php echo e($to); ?>"/></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Tampilkan</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Sumber</th><th class="text-end">Material</th><th class="text-end">Total</th><th class="text-end">Per porsi</th></tr></thead>
<tbody>
<?php $__currentLoopData = $costings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td class="text-secondary"><?php echo e($c->costing_date); ?></td><td><?php echo e($c->productionOrder->number ?? '-'); ?></td><td class="text-end"><?php echo e(mbg_currency($c->material_cost)); ?></td><td class="text-end"><?php echo e(mbg_currency($c->total_cost)); ?></td><td class="text-end"><?php echo e(mbg_currency($c->cost_per_portion)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/reports/financial.blade.php ENDPATH**/ ?>
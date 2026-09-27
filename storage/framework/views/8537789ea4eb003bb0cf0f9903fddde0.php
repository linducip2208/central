<?php $__env->startSection('title', 'Laporan Kedaluwarsa'); ?>
<?php $__env->startSection('subtitle', 'Batch expired / kedaluwarsa dalam ' . $days . ' hari'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><select name="days" class="form-select" onchange="this.form.submit()"><?php $__currentLoopData = [7,14,30,60,90]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d); ?>" <?php if($days == $d): echo 'selected'; endif; ?>>≤ <?php echo e($d); ?> hari</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Batch</th><th>Gudang</th><th>Expired</th><th class="text-end">Sisa</th><th>Sisa hari</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td class="fw-bold"><?php echo e($b->batch_no); ?></td><td class="text-secondary"><?php echo e($b->warehouse->name ?? ''); ?></td><td><?php echo e($b->expiry_date); ?></td><td class="text-end"><?php echo e(number_format($b->remaining_qty, 2)); ?></td><td><?php if(\Carbon\Carbon::parse($b->expiry_date)->isPast()): ?><span class="badge bg-red-lt">EXPIRED</span><?php else: ?><span class="badge bg-yellow-lt"><?php echo e(\Carbon\Carbon::parse($b->expiry_date)->diffInDays(now())); ?> hari</span><?php endif; ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="text-center text-secondary py-3">Tidak ada batch kritis.</td></tr>
<?php endif; ?>
</tbody></table></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/reports/expiry.blade.php ENDPATH**/ ?>
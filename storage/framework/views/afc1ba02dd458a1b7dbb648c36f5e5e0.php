<?php $__env->startSection('title', 'Laporan'); ?>
<?php $__env->startSection('content'); ?>
<div class="row row-deck row-cards">
<?php
$cards = [
['Stok & nilai persediaan', 'reports.stock', 'ti-box', 'Posisi stok per gudang + valuasi.', []],
['Produksi', 'reports.production', 'ti-chef-hat', 'Hasil produksi per periode.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Pengiriman & fulfillment', 'reports.delivery', 'ti-truck', 'Terkirim vs rencana per sekolah.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Keuangan', 'reports.financial', 'ti-coins', 'Biaya produksi, belanja, rugi waste.', ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()]],
['Kedaluarsa', 'reports.expiry', 'ti-alarm', 'Batch mendekati expired.', ['days' => 30]],
];
?>
<?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$title, $route, $icon, $desc, $params]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="col-md-4">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center mb-2"><span class="avatar bg-blue-lt me-2"><i class="ti <?php echo e($icon); ?>"></i></span><h3 class="card-title mb-0"><?php echo e($title); ?></h3></div>
<p class="text-secondary"><?php echo e($desc); ?></p>
<a href="<?php echo e(route($route, $params)); ?>" class="btn btn-white">Buka laporan</a>
</div></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\reports\index.blade.php ENDPATH**/ ?>
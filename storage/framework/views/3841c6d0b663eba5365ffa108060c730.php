<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('subtitle', 'Ringkasan operasional hari ini — ' . now()->translatedFormat('l, d F Y')); ?>

<?php $__env->startSection('content'); ?>
<div class="row row-deck row-cards mb-3">
<div class="col-sm-6 col-lg-3">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-green-lt me-3"><i class="ti ti-bowl"></i></span>
<div><div class="text-secondary">Porsi diproduksi hari ini</div><div class="h1 mb-0"><?php echo e(number_format($stats['portions_today'])); ?></div></div></div>
</div></div>
</div>
<div class="col-sm-6 col-lg-3">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-blue-lt me-3"><i class="ti ti-shopping-cart"></i></span>
<div><div class="text-secondary">PO aktif</div><div class="h1 mb-0"><?php echo e(number_format($stats['active_pos'])); ?></div></div></div>
</div></div>
</div>
<div class="col-sm-6 col-lg-3">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-yellow-lt me-3"><i class="ti ti-truck"></i></span>
<div><div class="text-secondary">Pengiriman berjalan</div><div class="h1 mb-0"><?php echo e(number_format($stats['in_transit'])); ?></div></div></div>
</div></div>
</div>
<div class="col-sm-6 col-lg-3">
<div class="card"><div class="card-body">
<div class="d-flex align-items-center"><span class="avatar bg-purple-lt me-3"><i class="ti ti-school"></i></span>
<div><div class="text-secondary">Sekolah dilayani</div><div class="h1 mb-0"><?php echo e(number_format($stats['schools'])); ?></div></div></div>
</div></div>
</div>
</div>

<div class="row row-deck row-cards">
<div class="col-lg-8">
<div class="card">
<div class="card-header"><h3 class="card-title">Produksi 7 hari terakhir (porsi)</h3></div>
<div class="card-body"><canvas id="chartProd" height="120"></canvas></div>
</div>
<div class="card mt-3">
<div class="card-header"><h3 class="card-title">Mutasi stok terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Gudang</th><th>Tipe</th><th>Item</th><th class="text-end">Qty</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $recentMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
<td class="text-secondary"><?php echo e($m->created_at->format('d M H:i')); ?></td>
<td><?php echo e($m->warehouse->name ?? '-'); ?></td>
<td><span class="badge bg-blue-lt"><?php echo e($m->movement_type); ?></span></td>
<td><?php echo e($m->item_type); ?> #<?php echo e($m->item_id); ?></td>
<td class="text-end <?php echo e($m->direction === 'IN' ? 'text-green' : 'text-red'); ?>"><?php echo e($m->direction === 'IN' ? '+' : '-'); ?><?php echo e(number_format($m->qty, 2)); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr><td colspan="5" class="text-center text-secondary py-4">Belum ada mutasi.</td></tr>
<?php endif; ?>
</tbody>
</table></div>
</div>
</div>
<div class="col-lg-4">
<div class="card">
<div class="card-header"><h3 class="card-title">Stok menipis</h3></div>
<div class="list-group list-group-flush">
<?php $__empty_1 = true; $__currentLoopData = $lowStock; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div class="list-group-item d-flex justify-content-between align-items-center">
<div><div class="fw-bold"><?php echo e($row['ingredient']->name); ?></div><div class="text-secondary small">Min. <?php echo e(number_format($row['min'], 2)); ?> <?php echo e($row['ingredient']->unit->symbol ?? ''); ?></div></div>
<span class="badge bg-red-lt"><?php echo e(number_format($row['stock'], 2)); ?></span>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div class="list-group-item text-secondary">Semua stok aman.</div>
<?php endif; ?>
</div>
</div>
<div class="card mt-3">
<div class="card-header"><h3 class="card-title">Mendekati kedaluwarsa</h3></div>
<div class="list-group list-group-flush">
<?php $__empty_1 = true; $__currentLoopData = $expiring; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div class="list-group-item d-flex justify-content-between align-items-center">
<div><div class="fw-bold"><?php echo e($b->batch_no); ?></div><div class="text-secondary small"><?php echo e($b->warehouse->name ?? ''); ?> · sisa <?php echo e(number_format($b->remaining_qty, 2)); ?></div></div>
<span class="badge bg-yellow-lt"><?php echo e($b->expiry_date?->format('d M Y')); ?></span>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div class="list-group-item text-secondary">Tidak ada batch kritis.</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const ctx = document.getElementById('chartProd');
if (ctx) {
new Chart(ctx, {type: 'bar',
data: {labels: <?php echo json_encode($production7->map(fn($r) => \Carbon\Carbon::parse($r->production_date)->format('d M'))->values()); ?>,
datasets: [{data: <?php echo json_encode($production7->pluck('qty')->values()); ?>, backgroundColor: '#2fb344', borderRadius: 4}]},
options: {plugins: {legend: {display: false}}, scales: {y: {beginAtZero: true}}}});
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\dashboard.blade.php ENDPATH**/ ?>
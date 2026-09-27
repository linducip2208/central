<?php $__env->startSection('title', $recipe->name); ?>
<?php $__env->startSection('subtitle', $recipe->code . ' · ' . ($recipe->product->name ?? '') . ' · yield ' . $recipe->yield_qty); ?>
<?php $__env->startSection('actions'); ?>
<form method="POST" action="<?php echo e(route('recipes.toggle', $recipe)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-white" type="submit"><?php echo e($recipe->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?></button></form>
<form method="POST" action="<?php echo e(route('recipes.destroy', $recipe)); ?>" class="d-inline" onsubmit="return confirm('Hapus resep?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-outline-danger" type="submit">Hapus</button></form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Komposisi (per <?php echo e($recipe->yield_qty); ?> hasil)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Qty</th><th>Satuan</th><th class="text-end">Susut</th><th class="text-end">Est. biaya</th></tr></thead>
<tbody>
<?php $total = 0; ?>
<?php $__currentLoopData = $recipe->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $cost = $it->qty * ($it->ingredient->standard_price ?? 0); $total += $cost; ?>
<tr><td><?php echo e($it->ingredient->name ?? '-'); ?></td><td class="text-end"><?php echo e(number_format($it->qty, 4)); ?></td><td><?php echo e($it->unit->symbol ?? ''); ?></td><td class="text-end"><?php echo e($it->waste_factor_pct); ?>%</td><td class="text-end"><?php echo e(mbg_currency($cost)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<tr class="fw-bold"><td colspan="4">Estimasi biaya bahan / yield</td><td class="text-end"><?php echo e(mbg_currency($total)); ?></td></tr>
</tbody></table></div></div>
<?php if($recipe->instructions): ?><div class="card mt-3"><div class="card-header"><h3 class="card-title">Instruksi</h3></div><div class="card-body"><?php echo e($recipe->instructions); ?></div></div><?php endif; ?>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-body">
<dl class="row small">
<dt class="col-6">Versi</dt><dd class="col-6"><?php echo e($recipe->version); ?></dd>
<dt class="col-6">Waktu masak</dt><dd class="col-6"><?php echo e($recipe->cook_time_minutes); ?> mnt</dd>
<dt class="col-6">Status</dt><dd class="col-6"><?php if($recipe->is_active): ?><span class="badge bg-green-lt">AKTIF</span><?php else: ?><span class="badge bg-secondary-lt">NONAKTIF</span><?php endif; ?></dd>
</dl>
</div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\recipes\show.blade.php ENDPATH**/ ?>
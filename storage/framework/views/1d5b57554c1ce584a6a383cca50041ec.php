<?php $__env->startSection('title', $ingredient->name); ?>
<?php $__env->startSection('subtitle', $ingredient->code . ' · ' . $ingredient->category . ' · per ' . ($ingredient->unit->symbol ?? '')); ?>
<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('ingredients.edit', $ingredient)); ?>" class="btn btn-white">Ubah</a>
<a href="<?php echo e(route('ingredients.index')); ?>" class="btn btn-ghost-secondary">Kembali</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<dl class="row small">
<dt class="col-5">Harga standar</dt><dd class="col-7"><?php echo e(mbg_currency($ingredient->standard_price)); ?></dd>
<dt class="col-5">Min / Max stok</dt><dd class="col-7"><?php echo e(number_format($ingredient->min_stock, 2)); ?> / <?php echo e(number_format($ingredient->max_stock, 2)); ?></dd>
<dt class="col-5">Daya simpan</dt><dd class="col-7"><?php echo e($ingredient->shelf_life_days); ?> hari</dd>
<dt class="col-5">Total stok</dt><dd class="col-7 fw-bold"><?php echo e(number_format($stocks->sum('qty'), 2)); ?> <?php echo e($ingredient->unit->symbol ?? ''); ?></dd>
</dl>
<form method="POST" action="<?php echo e(route('ingredients.destroy', $ingredient)); ?>" onsubmit="return confirm('Hapus bahan ini?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Stok per gudang & batch</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Batch</th><th>Expired</th><th class="text-end">Qty</th><th class="text-end">Tertahan</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><?php echo e($s->warehouse->name ?? '-'); ?></td><td><?php echo e($s->batch->batch_no ?? '-'); ?></td><td class="text-secondary"><?php echo e($s->batch->expiry_date ?? '-'); ?></td><td class="text-end"><?php echo e(number_format($s->qty, 2)); ?></td><td class="text-end text-secondary"><?php echo e(number_format($s->reserved_qty, 2)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="text-center text-secondary py-3">Tidak ada stok.</td></tr><?php endif; ?>
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Mutasi terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>Tipe</th><th>Ref</th><th class="text-end">Qty</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td class="text-secondary"><?php echo e($m->movement_date); ?></td><td><span class="badge bg-blue-lt"><?php echo e($m->movement_type); ?></span></td><td class="text-secondary"><?php echo e($m->reference_no ?? '-'); ?></td><td class="text-end <?php echo e($m->direction === 'IN' ? 'text-green' : 'text-red'); ?>"><?php echo e($m->direction === 'IN' ? '+' : '-'); ?><?php echo e(number_format($m->qty, 2)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4" class="text-center text-secondary py-3">Belum ada mutasi.</td></tr><?php endif; ?>
</tbody></table></div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/ingredients/show.blade.php ENDPATH**/ ?>
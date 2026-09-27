<?php $__env->startSection('title', $product->name); ?>
<?php $__env->startSection('subtitle', $product->code . ' · ' . $product->category); ?>
<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('recipes.create')); ?>" class="btn btn-white">Buat resep</a>
<a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-white">Ubah</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<?php if($product->description): ?><p class="text-secondary"><?php echo e($product->description); ?></p><?php endif; ?>
<dl class="row small">
<dt class="col-5">Porsi</dt><dd class="col-7"><?php echo e(number_format($product->portion_size_gram)); ?> g</dd>
<dt class="col-5">Stok jadi</dt><dd class="col-7 fw-bold"><?php echo e(number_format($stocks->sum('qty'), 0)); ?> <?php echo e($product->unit->symbol ?? ''); ?></dd>
<dt class="col-5">Resep aktif</dt><dd class="col-7"><?php echo e($product->activeRecipe->name ?? '— belum ada —'); ?> <?php echo e($product->activeRecipe ? '(v'.$product->activeRecipe->version.')' : ''); ?></dd>
</dl>
<form method="POST" action="<?php echo e(route('products.destroy', $product)); ?>" onsubmit="return confirm('Hapus produk ini?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button></form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Resep (<?php echo e($product->recipes->count()); ?>)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Yield</th><th>Aktif</th><th></th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $product->recipes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td class="text-secondary"><?php echo e($r->code); ?></td><td><a href="<?php echo e(route('recipes.show', $r)); ?>"><?php echo e($r->name); ?></a></td><td><?php echo e($r->yield_qty); ?></td><td><?php if($r->is_active): ?><span class="badge bg-green-lt">AKTIF</span><?php else: ?><span class="badge bg-secondary-lt">NONAKTIF</span><?php endif; ?></td><td class="text-end"><a class="btn btn-sm btn-white" href="<?php echo e(route('recipes.show', $r)); ?>">Buka</a></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="text-center text-secondary py-3">Belum ada resep.</td></tr><?php endif; ?>
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Stok produk jadi</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Batch</th><th class="text-end">Qty</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><?php echo e($s->warehouse->name ?? '-'); ?></td><td><?php echo e($s->batch->batch_no ?? '-'); ?></td><td class="text-end"><?php echo e(number_format($s->qty, 0)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="3" class="text-center text-secondary py-3">Tidak ada stok.</td></tr><?php endif; ?>
</tbody></table></div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\products\show.blade.php ENDPATH**/ ?>
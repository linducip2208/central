<?php $__env->startSection('title', 'Stok Inventory'); ?>
<?php $__env->startSection('actions'); ?><a href="<?php echo e(route('inventory.adjust.form')); ?>" class="btn btn-white">Penyesuaian</a><a href="<?php echo e(route('inventory.transfer.form')); ?>" class="btn btn-white">Transfer</a><a href="<?php echo e(route('inventory.reserve.form')); ?>" class="btn btn-white">Reservasi</a><a href="<?php echo e(route('inventory.movements')); ?>" class="btn btn-white">Ledger mutasi</a><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><select name="warehouse_id" class="form-select" onchange="this.form.submit()"><option value="">— Semua gudang —</option><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>" <?php if(request('warehouse_id') == $w->id): echo 'selected'; endif; ?>><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><select name="item_type" class="form-select" onchange="this.form.submit()"><option value="">— Bahan & produk —</option><option value="ingredient" <?php if(request('item_type') === 'ingredient'): echo 'selected'; endif; ?>>Bahan baku</option><option value="product" <?php if(request('item_type') === 'product'): echo 'selected'; endif; ?>>Produk jadi</option></select></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Gudang</th><th>Tipe</th><th>Item</th><th>Batch</th><th class="text-end">Qty</th><th class="text-end">Tertahan</th><th class="text-end">Tersedia</th><th class="text-end">Avg cost</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
<td class="text-secondary"><?php echo e($s->warehouse->name ?? '-'); ?></td>
<td><span class="badge bg-blue-lt"><?php echo e($s->item_type); ?></span></td>
<td><?php echo e($s->item_name ?? $s->item_type.' #'.$s->item_id); ?></td>
<td class="text-secondary"><?php echo e($s->batch->batch_no ?? '-'); ?></td>
<td class="text-end"><?php echo e(number_format($s->qty, 2)); ?></td>
<td class="text-end text-secondary"><?php echo e(number_format($s->reserved_qty, 2)); ?></td>
<td class="text-end fw-bold"><?php echo e(number_format($s->qty - $s->reserved_qty, 2)); ?></td>
<td class="text-end"><?php echo e(mbg_currency($s->avg_cost)); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="8"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'Tidak ada stok']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tidak ada stok']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $attributes = $__attributesOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__attributesOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f22a152e0729cd34293e65bd200d933)): ?>
<?php $component = $__componentOriginal4f22a152e0729cd34293e65bd200d933; ?>
<?php unset($__componentOriginal4f22a152e0729cd34293e65bd200d933); ?>
<?php endif; ?></td></tr>
<?php endif; ?>
</tbody></table></div>
<div class="mt-3"><?php echo e($stocks->links()); ?></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/inventory/index.blade.php ENDPATH**/ ?>
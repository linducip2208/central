<?php $__env->startSection('title', 'Costing'); ?>
<?php $__env->startSection('content'); ?>
<div class="row row-deck row-cards mb-3">
<div class="col-sm-6"><div class="card"><div class="card-body"><div class="text-secondary">Total biaya (filter saat ini)</div><div class="h1"><?php echo e(mbg_currency($summary['total'] ?? 0)); ?></div></div></div></div>
<div class="col-sm-6"><div class="card"><div class="card-body"><div class="text-secondary">Rata-rata per porsi</div><div class="h1"><?php echo e(mbg_currency($summary['avg_per_portion'] ?? 0)); ?></div></div></div></div>
</div>
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tanggal</th><th>WO / Menu</th><th class="text-end">Material</th><th class="text-end">Tenaga</th><th class="text-end">Overhead</th><th class="text-end">Total</th><th class="text-end">Porsi</th><th class="text-end">Per porsi</th><th></th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $costings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
<td class="text-secondary"><?php echo e($c->costing_date); ?></td>
<td><?php echo e($c->productionOrder->number ?? $c->menu->name ?? '-'); ?></td>
<td class="text-end"><?php echo e(mbg_currency($c->material_cost)); ?></td>
<td class="text-end"><?php echo e(mbg_currency($c->labor_cost)); ?></td>
<td class="text-end"><?php echo e(mbg_currency($c->overhead_cost)); ?></td>
<td class="text-end fw-bold"><?php echo e(mbg_currency($c->total_cost)); ?></td>
<td class="text-end"><?php echo e(number_format($c->portions)); ?></td>
<td class="text-end"><?php echo e(mbg_currency($c->cost_per_portion)); ?></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="<?php echo e(route('costings.show', $c)); ?>">Detail</a></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="9"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'Belum ada data costing. Costing dibuat otomatis saat WO diselesaikan.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Belum ada data costing. Costing dibuat otomatis saat WO diselesaikan.']); ?>
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
<div class="mt-3"><?php echo e($costings->links()); ?></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\costings\index.blade.php ENDPATH**/ ?>
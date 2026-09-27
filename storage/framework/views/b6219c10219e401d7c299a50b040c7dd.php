<?php $__env->startSection('title', 'Opname ' . $opname->number); ?>
<?php $__env->startSection('subtitle', ($opname->warehouse->name ?? '-') . ' · ' . $opname->opname_date); ?>
<?php $__env->startSection('actions'); ?>
<?php if($opname->status === 'COUNTED'): ?>
<form method="POST" action="<?php echo e(route('stock-opnames.approve', $opname)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-success" type="submit">Setujui</button></form>
<?php endif; ?>
<?php if($opname->status === 'APPROVED'): ?>
<form method="POST" action="<?php echo e(route('stock-opnames.post', $opname)); ?>" class="d-inline" onsubmit="return confirm('Posting selisih ke ledger? Tidak dapat dibatalkan.')"><?php echo csrf_field(); ?><button class="btn btn-warning" type="submit">Posting ke stok</button></form>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-header"><h3 class="card-title">Hasil hitung <span class="ms-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $opname->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($opname->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></span></h3></div>
<div class="card-body">
<?php if($opname->isEditable()): ?>
<form method="POST" action="<?php echo e(route('stock-opnames.count', $opname)); ?>"><?php echo csrf_field(); ?>
<?php endif; ?>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Item</th><th>Batch</th><th class="text-end">Sistem</th><th class="text-end">Fisik</th><th class="text-end">Selisih</th></tr></thead>
<tbody>
<?php $__currentLoopData = $opname->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
$name = $it->item_type === 'product' ? ($pnames[$it->item_id] ?? 'Produk #'.$it->item_id) : ($names[$it->item_id] ?? 'Bahan #'.$it->item_id);
$diff = $it->physical_qty - $it->system_qty;
?>
<tr>
<td><?php echo e($name); ?> <span class="text-secondary small"><?php echo e($it->item_type); ?></span></td>
<td class="text-secondary"><?php echo e($it->batch->batch_no ?? '-'); ?></td>
<td class="text-end text-secondary"><?php echo e(number_format($it->system_qty, 2)); ?></td>
<td class="text-end" style="min-width:140px">
<?php if($opname->isEditable()): ?>
<input name="counts[<?php echo e($it->id); ?>]" type="number" step="0.001" min="0" value="<?php echo e($it->physical_qty); ?>" class="form-control form-control-sm text-end"/>
<?php else: ?>
<?php echo e(number_format($it->physical_qty, 2)); ?>

<?php endif; ?>
</td>
<td class="text-end fw-bold <?php echo e($diff == 0 ? 'text-secondary' : ($diff > 0 ? 'text-green' : 'text-red')); ?>"><?php echo e($diff > 0 ? '+' : ''); ?><?php echo e(number_format($diff, 2)); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table></div>
<?php if($opname->isEditable()): ?>
<button class="btn btn-primary mt-3" type="submit">Simpan hasil hitung</button>
</form>
<?php endif; ?>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\stock-opnames\show.blade.php ENDPATH**/ ?>
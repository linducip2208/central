<?php $__env->startSection('title', 'Batch & Expired'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3"><input name="q" class="form-control" placeholder="No. batch…" value="<?php echo e(request('q')); ?>"/></div>
<div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()"><option value="">— Semua status —</option><?php $__currentLoopData = ['AVAILABLE','BLOCKED','EXPIRED','DEPLETED']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s); ?>" <?php if(request('status') === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><select name="expiring" class="form-select" onchange="this.form.submit()"><option value="">— Kedaluwarsa —</option><option value="7" <?php if(request('expiring') == '7'): echo 'selected'; endif; ?>>≤ 7 hari</option><option value="30" <?php if(request('expiring') == '30'): echo 'selected'; endif; ?>>≤ 30 hari</option><option value="90" <?php if(request('expiring') == '90'): echo 'selected'; endif; ?>>≤ 90 hari</option></select></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Filter</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Batch</th><th>Item</th><th>Gudang</th><th>Produksi</th><th>Expired</th><th class="text-end">Sisa</th><th>Status</th><th></th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
<td class="fw-bold"><?php echo e($b->batch_no); ?></td>
<td><?php echo e($b->item_name ?? $b->item_type.' #'.$b->item_id); ?></td>
<td class="text-secondary"><?php echo e($b->warehouse->name ?? '-'); ?></td>
<td class="text-secondary"><?php echo e($b->production_date ?? '-'); ?></td>
<td><?php if($b->expiry_date): ?><span class="badge <?php echo e(\Carbon\Carbon::parse($b->expiry_date)->isPast() ? 'bg-red-lt' : (\Carbon\Carbon::parse($b->expiry_date)->diffInDays(now()) <= 30 ? 'bg-yellow-lt' : 'bg-green-lt')); ?>"><?php echo e($b->expiry_date); ?></span><?php else: ?><span class="text-secondary">—</span><?php endif; ?></td>
<td class="text-end"><?php echo e(number_format($b->remaining_qty, 2)); ?></td>
<td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $b->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($b->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td>
<td class="text-end">
<?php if($b->status === 'AVAILABLE'): ?><form method="POST" action="<?php echo e(route('batches.block', $b)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-sm btn-white" type="submit">Blokir</button></form><?php endif; ?>
<?php if($b->status === 'BLOCKED'): ?><form method="POST" action="<?php echo e(route('batches.unblock', $b)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-sm btn-white" type="submit">Buka</button></form><?php endif; ?>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="8"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'Tidak ada batch']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tidak ada batch']); ?>
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
<div class="mt-3"><?php echo e($batches->links()); ?></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\batches\index.blade.php ENDPATH**/ ?>
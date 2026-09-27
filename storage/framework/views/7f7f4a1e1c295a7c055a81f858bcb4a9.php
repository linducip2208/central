<?php $__env->startSection('title', 'PR ' . $pr->number); ?>
<?php $__env->startSection('subtitle', 'Dapur: ' . ($pr->centralKitchen->name ?? '-') . ' · Diminta: ' . ($pr->requester->name ?? '-')); ?>
<?php $__env->startSection('actions'); ?>
<?php if($pr->status === 'DRAFT'): ?>
<form method="POST" action="<?php echo e(route('purchase-requests.submit', $pr)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-primary" type="submit">Submit</button></form>
<form method="POST" action="<?php echo e(route('purchase-requests.destroy', $pr)); ?>" class="d-inline" onsubmit="return confirm('Hapus PR?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-ghost-danger" type="submit">Hapus</button></form>
<?php endif; ?>
<a href="<?php echo e(route('purchase-orders.create')); ?>" class="btn btn-white">Buatkan PO</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Item <span class="ms-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $pr->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pr->status)]); ?>
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
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Bahan</th><th class="text-end">Diminta</th><th class="text-end">Disetujui</th><th class="text-end">Dipesan</th><th class="text-end">Est. harga</th></tr></thead>
<tbody>
<?php $__currentLoopData = $pr->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td><?php echo e($it->ingredient->name ?? '-'); ?> <span class="text-secondary">(<?php echo e($it->ingredient->unit->symbol ?? ''); ?>)</span></td>
<td class="text-end"><?php echo e(number_format($it->qty_requested, 2)); ?></td>
<td class="text-end"><?php echo e(number_format($it->qty_approved, 2)); ?></td>
<td class="text-end"><?php echo e(number_format($it->qty_ordered, 2)); ?></td>
<td class="text-end"><?php echo e(mbg_currency($it->estimated_price)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table></div></div>
</div>
<div class="col-lg-4">
<?php if($pr->status === 'SUBMITTED'): ?>
<div class="card"><div class="card-header"><h3 class="card-title">Persetujuan</h3></div>
<div class="card-body">
<form method="POST" action="<?php echo e(route('purchase-requests.approve', $pr)); ?>"><?php echo csrf_field(); ?>
<?php $__currentLoopData = $pr->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="mb-2"><label class="form-label"><?php echo e($it->ingredient->name); ?> (minta <?php echo e(number_format($it->qty_requested, 2)); ?>)</label>
<input name="approved[<?php echo e($it->id); ?>]" type="number" step="0.001" min="0" value="<?php echo e($it->qty_requested); ?>" class="form-control"/></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<button class="btn btn-success w-100" type="submit">Setujui</button>
</form>
<form method="POST" action="<?php echo e(route('purchase-requests.reject', $pr)); ?>" class="mt-2"><?php echo csrf_field(); ?>
<div class="input-group"><input name="reject_reason" class="form-control" placeholder="Alasan penolakan" required/><button class="btn btn-danger" type="submit">Tolak</button></div>
</form>
</div></div>
<?php else: ?>
<div class="card"><div class="card-body">
<dl class="row small mb-0">
<dt class="col-5">Status</dt><dd class="col-7"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $pr->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pr->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></dd>
<dt class="col-5">Disetujui oleh</dt><dd class="col-7"><?php echo e($pr->approver->name ?? '-'); ?></dd>
<dt class="col-5">Alasan tolak</dt><dd class="col-7"><?php echo e($pr->reject_reason ?? '-'); ?></dd>
<dt class="col-5">Catatan</dt><dd class="col-7"><?php echo e($pr->notes ?? '-'); ?></dd>
</dl>
</div></div>
<?php endif; ?>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/purchase-requests/show.blade.php ENDPATH**/ ?>
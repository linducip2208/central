<?php $__env->startSection('title', 'WO ' . $order->number); ?>
<?php $__env->startSection('subtitle', ($order->product->name ?? '-') . ' · ' . number_format($order->planned_qty, 0) . ' rencana · ' . number_format($order->produced_qty, 0) . ' hasil'); ?>
<?php $__env->startSection('actions'); ?>
<?php if($order->status === 'PLANNED'): ?>
<form method="POST" action="<?php echo e(route('production-orders.release', $order)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-primary" type="submit">Rilis ke dapur</button></form>
<form method="POST" action="<?php echo e(route('production-orders.cancel', $order)); ?>" class="d-inline" onsubmit="return confirm('Batalkan order?')"><?php echo csrf_field(); ?><button class="btn btn-ghost-danger" type="submit">Batal</button></form>
<?php endif; ?>
<?php if(in_array($order->status, ['RELEASED','PARTIAL'])): ?>
<form method="POST" action="<?php echo e(route('production-orders.start', $order)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-primary" type="submit">Mulai masak</button></form>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Kebutuhan bahan <span class="ms-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $order->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status)]); ?>
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
<thead><tr><th>Bahan</th><th class="text-end">Butuh</th><th class="text-end">Terpakai</th><th class="text-end">Sisa</th></tr></thead>
<tbody>
<?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td><?php echo e($it->ingredient->name ?? '-'); ?></td><td class="text-end"><?php echo e(number_format($it->qty_required, 2)); ?></td><td class="text-end"><?php echo e(number_format($it->qty_consumed, 2)); ?></td><td class="text-end fw-bold"><?php echo e(number_format($it->qty_required - $it->qty_consumed, 2)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table></div></div>

<?php if(in_array($order->status, ['RELEASED','IN_PROGRESS','PARTIAL'])): ?>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Konsumsi bahan (FEFO, boleh parsial)</h3></div>
<div class="card-body">
<form method="POST" action="<?php echo e(route('production-orders.consume', $order)); ?>"><?php echo csrf_field(); ?>
<div class="mb-2"><label class="form-label">Gudang asal</label>
<select name="warehouse_id" class="form-select"><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="row g-2 mb-2">
<div class="col-7"><input class="form-control" value="<?php echo e($it->ingredient->name); ?>" disabled/><input type="hidden" name="items[<?php echo e($loop->index); ?>][order_item_id]" value="<?php echo e($it->id); ?>"/></div>
<div class="col-5"><input name="items[<?php echo e($loop->index); ?>][qty]" type="number" step="0.001" min="0" max="<?php echo e(max(0, $it->qty_required - $it->qty_consumed)); ?>" value="<?php echo e(max(0, $it->qty_required - $it->qty_consumed)); ?>" class="form-control"/></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<button class="btn btn-warning" type="submit">Catat konsumsi</button>
</form>
</div></div>

<div class="card mt-3"><div class="card-header"><h3 class="card-title">Selesaikan & catat hasil</h3></div>
<div class="card-body">
<form method="POST" action="<?php echo e(route('production-orders.complete', $order)); ?>"><?php echo csrf_field(); ?>
<div class="row g-2">
<div class="col-md-4"><label class="form-label">Gudang hasil</label>
<select name="warehouse_id" class="form-select"><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><label class="form-label">Hasil baik</label><input name="produced_qty" type="number" step="0.001" min="0" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Reject</label><input name="rejected_qty" type="number" step="0.001" min="0" value="0" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Expired hasil</label><input name="expiry_date" type="date" class="form-control" value="<?php echo e(now()->addDay()->toDateString()); ?>"/></div>
<div class="col-md-3"><label class="form-label">Biaya tenaga</label><input name="labor_cost" type="number" min="0" value="0" class="form-control"/></div>
<div class="col-md-3"><label class="form-label">Biaya overhead</label><input name="overhead_cost" type="number" min="0" value="0" class="form-control"/></div>
</div>
<button class="btn btn-success mt-2" type="submit">Selesai + masuk stok</button>
</form>
</div></div>
<?php endif; ?>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-body">
<div class="progress mb-2"><div class="progress-bar bg-green" style="width: <?php echo e(min(100, $order->completionPct())); ?>%"></div></div>
<p class="text-secondary"><?php echo e($order->completionPct()); ?>% selesai · reject <?php echo e(number_format($order->rejected_qty, 0)); ?></p>
<dl class="row small mb-0">
<dt class="col-5">Resep</dt><dd class="col-7"><?php echo e($order->recipe->name ?? '—'); ?></dd>
<dt class="col-5">Unit dapur</dt><dd class="col-7"><?php echo e($order->kitchenUnit->name ?? '—'); ?></dd>
<dt class="col-5">Mulai</dt><dd class="col-7"><?php echo e($order->started_at ?? '—'); ?></dd>
<dt class="col-5">Selesai</dt><dd class="col-7"><?php echo e($order->completed_at ?? '—'); ?></dd>
<dt class="col-5">Catatan</dt><dd class="col-7"><?php echo e($order->notes ?? '—'); ?></dd>
</dl>
</div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/production-orders/show.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'PO ' . $po->number); ?>
<?php $__env->startSection('subtitle', ($po->supplier->name ?? '-') . ' · ' . mbg_currency($po->grand_total)); ?>
<?php $__env->startSection('actions'); ?>
<?php if($po->status === 'DRAFT'): ?>
<form method="POST" action="<?php echo e(route('purchase-orders.submit', $po)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-primary" type="submit">Submit</button></form>
<?php endif; ?>
<?php if($po->status === 'SUBMITTED'): ?>
<form method="POST" action="<?php echo e(route('purchase-orders.approve', $po)); ?>" class="d-inline"><?php echo csrf_field(); ?><button class="btn btn-success" type="submit">Setujui</button></form>
<form method="POST" action="<?php echo e(route('purchase-orders.reject', $po)); ?>" class="d-inline"><?php echo csrf_field(); ?><div class="input-group d-inline-flex" style="width:auto"><input name="reject_reason" class="form-control" placeholder="Alasan" required/><button class="btn btn-danger" type="submit">Tolak</button></div></form>
<?php endif; ?>
<?php if(in_array($po->status, ['APPROVED','PARTIAL'])): ?>
<a href="<?php echo e(route('goods-receipts.create')); ?>" class="btn btn-success">Terima barang (GR)</a>
<form method="POST" action="<?php echo e(route('purchase-orders.cancel', $po)); ?>" class="d-inline" onsubmit="return confirm('Batalkan PO?')"><?php echo csrf_field(); ?><button class="btn btn-ghost-danger" type="submit">Batal</button></form>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Item <span class="ms-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $po->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($po->status)]); ?>
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
<thead><tr><th>Bahan</th><th class="text-end">Dipesan</th><th class="text-end">Diterima</th><th class="text-end">Sisa</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr></thead>
<tbody>
<?php $__currentLoopData = $po->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td><?php echo e($it->ingredient->name ?? '-'); ?></td>
<td class="text-end"><?php echo e(number_format($it->qty_ordered, 2)); ?></td>
<td class="text-end"><?php echo e(number_format($it->qty_received, 2)); ?></td>
<td class="text-end fw-bold"><?php echo e(number_format($it->remainingToReceive(), 2)); ?></td>
<td class="text-end"><?php echo e(mbg_currency($it->unit_price)); ?></td>
<td class="text-end"><?php echo e(mbg_currency($it->line_total)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<tr class="fw-bold"><td colspan="5" class="text-end">Grand total</td><td class="text-end"><?php echo e(mbg_currency($po->grand_total)); ?></td></tr>
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Riwayat penerimaan</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor GR</th><th>Tanggal</th><th>Status</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $po->receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><a href="<?php echo e(route('goods-receipts.show', $gr)); ?>"><?php echo e($gr->number); ?></a></td><td class="text-secondary"><?php echo e($gr->receipt_date); ?></td><td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $gr->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($gr->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="3" class="text-center text-secondary py-3">Belum ada penerimaan.</td></tr><?php endif; ?>
</tbody></table></div></div>
</div>
<div class="col-lg-4">
<div class="card"><div class="card-body">
<dl class="row small mb-0">
<dt class="col-5">Dari PR</dt><dd class="col-7"><?php echo e($po->purchaseRequest->number ?? '-'); ?></dd>
<dt class="col-5">Gudang</dt><dd class="col-7"><?php echo e($po->warehouse->name ?? '-'); ?></dd>
<dt class="col-5">Ekspektasi</dt><dd class="col-7"><?php echo e($po->expected_date ?? '-'); ?></dd>
<dt class="col-5">Pembayaran</dt><dd class="col-7"><?php echo e($po->payment_terms); ?></dd>
<dt class="col-5">Catatan</dt><dd class="col-7"><?php echo e($po->notes ?? '-'); ?></dd>
</dl>
</div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\purchase-orders\show.blade.php ENDPATH**/ ?>
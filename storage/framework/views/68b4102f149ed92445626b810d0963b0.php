<?php $__env->startSection('title', 'Delivery ' . $delivery->number); ?>
<?php $__env->startSection('subtitle', ($delivery->school->name ?? '-') . ' · rencana ' . number_format($delivery->qty_planned) . ' porsi'); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Item <span class="ms-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $delivery->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($delivery->status)]); ?>
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
<thead><tr><th>Produk</th><th class="text-end">Rencana</th><th class="text-end">Terkirim</th><th class="text-end">Return</th></tr></thead>
<tbody>
<?php $__currentLoopData = $delivery->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td><?php echo e($it->product->name ?? '-'); ?></td><td class="text-end"><?php echo e(number_format($it->qty_planned)); ?></td><td class="text-end"><?php echo e(number_format($it->qty_delivered)); ?></td><td class="text-end"><?php echo e(number_format($it->qty_returned)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table></div></div>

<?php if(in_array($delivery->status, ['PLANNED','IN_TRANSIT'])): ?>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Serah terima (kurangi stok FEFO)</h3></div>
<div class="card-body">
<?php if($delivery->delivery_proof): ?>
<div class="card mb-3"><div class="card-header"><h3 class="card-title">Bukti serah terima</h3></div>
<div class="card-body"><a href="<?php echo e(Storage::url($delivery->delivery_proof)); ?>" target="_blank"><img src="<?php echo e(Storage::url($delivery->delivery_proof)); ?>" alt="Bukti" style="max-height:220px" class="rounded border"/></a></div></div>
<?php endif; ?>
<form method="POST" action="<?php echo e(route('deliveries.deliver', $delivery)); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
<div class="row g-2">
<div class="col-md-4"><label class="form-label">Gudang asal *</label>
<select name="warehouse_id" class="form-select" required><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><label class="form-label">Diterima *</label><input name="qty_delivered" type="number" min="0" max="<?php echo e($delivery->qty_planned); ?>" value="<?php echo e($delivery->qty_planned); ?>" class="form-control" required/></div>
<div class="col-md-2"><label class="form-label">Return</label><input name="qty_returned" type="number" min="0" value="0" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Diterima oleh *</label><input name="received_by_name" class="form-control" required placeholder="Nama penerima"/></div>
<div class="col-md-4"><label class="form-label">Suhu (°C)</label><input name="temperature_c" type="number" step="0.1" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Foto bukti (jpg/png, maks 4MB)</label><input name="proof" type="file" accept="image/*" class="form-control"/></div>
<div class="col-md-4"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<button class="btn btn-success mt-2" type="submit">Simpan serah terima</button>
</form>
<form method="POST" action="<?php echo e(route('deliveries.fail', $delivery)); ?>" class="mt-2"><?php echo csrf_field(); ?>
<div class="input-group"><input name="notes" class="form-control" placeholder="Alasan gagal" required/><button class="btn btn-danger" type="submit">Tandai gagal</button></div>
</form>
</div></div>
<?php endif; ?>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Tracking</h3></div>
<div class="list-group list-group-flush">
<?php $__empty_1 = true; $__currentLoopData = $delivery->trackings->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div class="list-group-item">
<div class="d-flex justify-content-between"><span class="badge bg-blue-lt"><?php echo e($t->status); ?></span><span class="text-secondary small"><?php echo e($t->created_at->format('d M H:i')); ?></span></div>
<div class="small mt-1"><?php echo e($t->notes ?? '-'); ?></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="list-group-item text-secondary">Belum ada tracking.</div><?php endif; ?>
</div>
<div class="card-body border-top">
<form method="POST" action="<?php echo e(route('deliveries.track', $delivery)); ?>"><?php echo csrf_field(); ?>
<div class="row g-2">
<div class="col-5"><input name="status" class="form-control" placeholder="Status *" required/></div>
<div class="col-7"><div class="input-group"><input name="notes" class="form-control" placeholder="Catatan"/><button class="btn btn-white" type="submit">Tambah</button></div></div>
</div>
</form>
</div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/deliveries/show.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', $school->name); ?>
<?php $__env->startSection('subtitle', $school->code . ' · ' . $school->level); ?>
<?php $__env->startSection('actions'); ?>
<a href="<?php echo e(route('schools.edit', $school)); ?>" class="btn btn-white">Ubah</a>
<a href="<?php echo e(route('schools.index')); ?>" class="btn btn-ghost-secondary">Kembali</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<div class="mb-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $school->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($school->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></div>
<dl class="row small">
<dt class="col-5">NPSN</dt><dd class="col-7"><?php echo e($school->npsn ?? '-'); ?></dd>
<dt class="col-5">Siswa</dt><dd class="col-7"><?php echo e(number_format($school->student_count)); ?></dd>
<dt class="col-5">Target porsi</dt><dd class="col-7"><?php echo e(number_format($school->target_portions)); ?>/hari</dd>
<dt class="col-5">Jarak</dt><dd class="col-7"><?php echo e($school->distance_km); ?> km</dd>
<dt class="col-5">PJ</dt><dd class="col-7"><?php echo e($school->pic_name ?? '-'); ?> (<?php echo e($school->pic_phone ?? '-'); ?>)</dd>
<dt class="col-5">Alamat</dt><dd class="col-7"><?php echo e($school->address ?? '-'); ?></dd>
</dl>
</div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Tambah penerima</h3></div>
<div class="card-body">
<form method="POST" action="<?php echo e(route('schools.recipients.store', $school)); ?>"><?php echo csrf_field(); ?>
<div class="mb-2"><input name="name" class="form-control" placeholder="Nama siswa *" required/></div>
<div class="row g-2">
<div class="col-6"><input name="identifier" class="form-control" placeholder="NIS/NISN"/></div>
<div class="col-3"><input name="grade" class="form-control" placeholder="Kelas"/></div>
<div class="col-3"><select name="gender" class="form-select"><option value="">L/P</option><option value="L">L</option><option value="P">P</option></select></div>
</div>
<div class="mt-2"><input name="allergy_notes" class="form-control" placeholder="Catatan alergi"/></div>
<button class="btn btn-primary btn-sm mt-2" type="submit">Tambah</button>
</form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Penerima (<?php echo e($school->recipients->count()); ?> ditampilkan)</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Alergi</th><th></th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $school->recipients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><?php echo e($r->name); ?></td><td class="text-secondary"><?php echo e($r->identifier ?? '-'); ?></td><td class="text-secondary"><?php echo e($r->grade); ?><?php echo e($r->class_name); ?></td><td class="text-secondary"><?php echo e($r->allergy_notes ?? '-'); ?></td>
<td class="text-end"><form method="POST" action="<?php echo e(route('recipients.destroy', $r)); ?>" onsubmit="return confirm('Hapus?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="text-center text-secondary py-3">Belum ada penerima.</td></tr><?php endif; ?>
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Pengiriman terakhir</h3></div>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nomor</th><th>Tanggal</th><th class="text-end">Terkirim</th><th>Status</th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $school->deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr><td><a href="<?php echo e(route('deliveries.show', $d)); ?>"><?php echo e($d->number); ?></a></td><td class="text-secondary"><?php echo e($d->delivery_date); ?></td><td class="text-end"><?php echo e(number_format($d->qty_delivered)); ?></td><td><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $d->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($d->status)]); ?>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4" class="text-center text-secondary py-3">Belum ada pengiriman.</td></tr><?php endif; ?>
</tbody></table></div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\schools\show.blade.php ENDPATH**/ ?>
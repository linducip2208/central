<?php $__env->startSection('title', 'Penerima Manfaat'); ?>
<?php $__env->startSection('subtitle', $allergyCount . ' penerima memiliki catatan alergi — perhatikan saat packing'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 mb-3">
<div class="col-md-4"><div class="input-icon"><span class="input-icon-addon"><i class="ti ti-search"></i></span><input type="text" name="q" class="form-control" placeholder="Nama / NIS…" value="<?php echo e(request('q')); ?>"/></div></div>
<div class="col-md-4"><select name="school_id" class="form-select" onchange="this.form.submit()"><option value="">— Semua sekolah —</option><?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(request('school_id') == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><label class="form-check mt-2"><input type="checkbox" name="allergy" value="1" class="form-check-input" <?php if(request('allergy')): echo 'checked'; endif; ?> onchange="this.form.submit()"/><span class="form-check-label">Alergi saja</span></label></div>
<div class="col-md-auto"><button class="btn btn-white" type="submit">Filter</button></div>
</form>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Nama</th><th>NIS</th><th>Sekolah</th><th>Kelas</th><th>L/P</th><th>Alergi</th><th>Status</th><th></th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $recipients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
<td><?php echo e($r->name); ?></td>
<td class="text-secondary"><?php echo e($r->identifier ?? '-'); ?></td>
<td class="text-secondary"><?php echo e($r->school->name ?? '-'); ?></td>
<td class="text-secondary"><?php echo e($r->grade); ?><?php echo e($r->class_name); ?></td>
<td><?php echo e($r->gender ?? '-'); ?></td>
<td><?php if($r->allergy_notes): ?><span class="badge bg-red-lt"><?php echo e($r->allergy_notes); ?></span><?php else: ?><span class="text-secondary">—</span><?php endif; ?></td>
<td><?php if($r->is_active): ?><span class="badge bg-green-lt">AKTIF</span><?php else: ?><span class="badge bg-secondary-lt">NONAKTIF</span><?php endif; ?></td>
<td class="text-end">
<form method="POST" action="<?php echo e(route('recipients.update', $r)); ?>" class="d-inline"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<input type="hidden" name="name" value="<?php echo e($r->name); ?>"/>
<input type="hidden" name="allergy_notes" value="<?php echo e($r->allergy_notes); ?>"/>
<input type="hidden" name="is_active" value="<?php echo e($r->is_active ? 0 : 1); ?>"/>
<button class="btn btn-sm btn-white" type="submit"><?php echo e($r->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?></button></form>
<form method="POST" action="<?php echo e(route('recipients.destroy', $r)); ?>" class="d-inline" onsubmit="return confirm('Hapus?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="8"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'Belum ada penerima']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Belum ada penerima']); ?>
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
<div class="mt-3"><?php echo e($recipients->links()); ?></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/recipients/index.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', ($school->exists ? 'Ubah' : 'Tambah') . ' Sekolah'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e($school->exists ? route('schools.update', $school) : route('schools.store')); ?>">
<?php echo csrf_field(); ?> <?php if($school->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama sekolah *</label><input name="name" class="form-control" value="<?php echo e(old('name', $school->name)); ?>" required/></div>
<div class="col-md-3"><label class="form-label">Jenjang *</label>
<select name="level" class="form-select"><?php $__currentLoopData = ['PAUD','TK','SD','SMP','SMA','SMK','SLB']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($l); ?>" <?php if(old('level', $school->level) === $l): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><label class="form-label">NPSN</label><input name="npsn" class="form-control" value="<?php echo e(old('npsn', $school->npsn)); ?>"/></div>
<div class="col-md-6"><label class="form-label">Central kitchen</label>
<select name="central_kitchen_id" class="form-select"><option value="">—</option><?php $__currentLoopData = $kitchens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k->id); ?>" <?php if(old('central_kitchen_id', $school->central_kitchen_id) == $k->id): echo 'selected'; endif; ?>><?php echo e($k->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><label class="form-label">Jumlah siswa *</label><input name="student_count" type="number" min="0" class="form-control" value="<?php echo e(old('student_count', $school->student_count ?? 0)); ?>" required/></div>
<div class="col-md-3"><label class="form-label">Target porsi/hari *</label><input name="target_portions" type="number" min="0" class="form-control" value="<?php echo e(old('target_portions', $school->target_portions ?? 0)); ?>" required/></div>
<div class="col-md-8"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2"><?php echo e(old('address', $school->address)); ?></textarea></div>
<div class="col-md-2"><label class="form-label">Kecamatan</label><input name="district" class="form-control" value="<?php echo e(old('district', $school->district)); ?>"/></div>
<div class="col-md-2"><label class="form-label">Kota</label><input name="city" class="form-control" value="<?php echo e(old('city', $school->city)); ?>"/></div>
<div class="col-md-4"><label class="form-label">Penanggung jawab</label><input name="pic_name" class="form-control" value="<?php echo e(old('pic_name', $school->pic_name)); ?>"/></div>
<div class="col-md-4"><label class="form-label">Telepon PJ</label><input name="pic_phone" class="form-control" value="<?php echo e(old('pic_phone', $school->pic_phone)); ?>"/></div>
<div class="col-md-2"><label class="form-label">Jarak (km)</label><input name="distance_km" type="number" step="0.1" min="0" class="form-control" value="<?php echo e(old('distance_km', $school->distance_km ?? 0)); ?>"/></div>
<div class="col-md-2"><label class="form-label">Status *</label>
<select name="status" class="form-select"><?php $__currentLoopData = ['ACTIVE','INACTIVE']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s); ?>" <?php if(old('status', $school->status ?? 'ACTIVE') === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
</div>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan</button><a href="<?php echo e(route('schools.index')); ?>" class="btn btn-white">Batal</a></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\schools\form.blade.php ENDPATH**/ ?>
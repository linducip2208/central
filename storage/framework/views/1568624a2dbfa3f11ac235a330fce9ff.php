<form method="GET" class="row g-2 mb-3">
<div class="col-md-4">
<div class="input-icon">
<span class="input-icon-addon"><i class="ti ti-search"></i></span>
<input type="text" name="q" class="form-control" placeholder="Cari…" value="<?php echo e(request('q')); ?>"/>
</div>
</div>
<?php if(!empty($statuses)): ?>
<div class="col-md-3">
<select name="status" class="form-select" onchange="this.form.submit()">
<option value="">— Semua status —</option>
<?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<option value="<?php echo e($s); ?>" <?php if(request('status') === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
</div>
<?php endif; ?>
<div class="col-md-auto">
<button class="btn btn-white" type="submit">Filter</button>
<?php if(request('q') || request('status')): ?>
<a href="<?php echo e(url()->current()); ?>" class="btn btn-ghost-secondary">Reset</a>
<?php endif; ?>
</div>
</form>
<?php /**PATH resources/views\components\filter.blade.php ENDPATH**/ ?>
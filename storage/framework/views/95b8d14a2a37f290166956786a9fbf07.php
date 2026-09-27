<?php $__env->startSection('title', $menu->name); ?>
<?php $__env->startSection('subtitle', $menu->code . ' · ' . \Carbon\Carbon::parse($menu->menu_date)->format('d M Y') . ' · ' . number_format($menu->planned_portions) . ' porsi'); ?>
<?php $__env->startSection('actions'); ?>
<?php if($menu->status === 'DRAFT'): ?>
<form method="POST" action="<?php echo e(route('menus.status', $menu)); ?>" class="d-inline"><?php echo csrf_field(); ?><input type="hidden" name="status" value="APPROVED"/><button class="btn btn-success" type="submit">Setujui</button></form>
<?php endif; ?>
<form method="POST" action="<?php echo e(route('menus.status', $menu)); ?>" class="d-inline"><?php echo csrf_field(); ?><input type="hidden" name="status" value="CANCELLED"/><button class="btn btn-ghost-danger" type="submit" onclick="return confirm('Batalkan menu?')">Batalkan</button></form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-header"><h3 class="card-title">Produk (<?php echo e($menu->items->count()); ?>) <span class="ms-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $menu->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($menu->status)]); ?>
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
<thead><tr><th>Produk</th><th class="text-end">Qty/porsi</th><th class="text-end">Total butuh</th></tr></thead>
<tbody>
<?php $__currentLoopData = $menu->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr><td><?php echo e($it->product->name ?? '-'); ?></td><td class="text-end"><?php echo e(number_format($it->qty_per_portion, 3)); ?></td><td class="text-end fw-bold"><?php echo e(number_format($it->qty_per_portion * $menu->planned_portions, 0)); ?></td></tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody></table></div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Gizi per porsi</h3></div>
<div class="card-body">
<?php if($menu->nutrition->isNotEmpty()): ?>
<?php $n = $menu->nutrition->first(); ?>
<dl class="row small mb-0">
<dt class="col-6">Kalori</dt><dd class="col-6"><?php echo e($n->calories); ?> kkal</dd>
<dt class="col-6">Protein</dt><dd class="col-6"><?php echo e($n->protein_g); ?> g</dd>
<dt class="col-6">Karbohidrat</dt><dd class="col-6"><?php echo e($n->carbs_g); ?> g</dd>
<dt class="col-6">Lemak</dt><dd class="col-6"><?php echo e($n->fat_g); ?> g</dd>
<dt class="col-6">Serat</dt><dd class="col-6"><?php echo e($n->fiber_g); ?> g</dd>
<dt class="col-6">Natrium</dt><dd class="col-6"><?php echo e($n->sodium_mg); ?> mg</dd>
</dl>
<?php else: ?>
<form method="POST" action="<?php echo e(route('menus.nutrition', $menu)); ?>"><?php echo csrf_field(); ?>
<div class="row g-2">
<div class="col-6"><input name="calories" type="number" step="0.01" class="form-control" placeholder="Kalori (kkal)"/></div>
<div class="col-6"><input name="protein_g" type="number" step="0.01" class="form-control" placeholder="Protein (g)"/></div>
<div class="col-6"><input name="carbs_g" type="number" step="0.01" class="form-control" placeholder="Karbo (g)"/></div>
<div class="col-6"><input name="fat_g" type="number" step="0.01" class="form-control" placeholder="Lemak (g)"/></div>
<div class="col-6"><input name="fiber_g" type="number" step="0.01" class="form-control" placeholder="Serat (g)"/></div>
<div class="col-6"><input name="serving_size_g" type="number" step="0.01" class="form-control" placeholder="Sajian (g)"/></div>
</div>
<button class="btn btn-primary btn-sm mt-2" type="submit">Simpan gizi</button>
</form>
<?php endif; ?>
</div></div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH resources/views\menus\show.blade.php ENDPATH**/ ?>
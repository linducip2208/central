<?php $__env->startSection('title', 'Satuan & Konversi'); ?>
<?php $__env->startSection('subtitle', 'Satuan dasar dipakai bahan/produk; konversi dipakai katering saat terima/consumption beda kemasan'); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-body">
<?php if (isset($component)) { $__componentOriginal2848fab3424fc8162748b5c6984d5047 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2848fab3424fc8162748b5c6984d5047 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.filter','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2848fab3424fc8162748b5c6984d5047)): ?>
<?php $attributes = $__attributesOriginal2848fab3424fc8162748b5c6984d5047; ?>
<?php unset($__attributesOriginal2848fab3424fc8162748b5c6984d5047); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2848fab3424fc8162748b5c6984d5047)): ?>
<?php $component = $__componentOriginal2848fab3424fc8162748b5c6984d5047; ?>
<?php unset($__componentOriginal2848fab3424fc8162748b5c6984d5047); ?>
<?php endif; ?>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Simbol</th><th>Tipe</th><th>Base</th><th>Aktif</th><th>Konversi</th><th></th></tr></thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
<td class="fw-bold"><?php echo e($u->code); ?></td>
<td><?php echo e($u->name); ?></td>
<td><?php echo e($u->symbol); ?></td>
<td class="text-secondary"><?php echo e($u->unit_type); ?></td>
<td><?php if($u->is_base): ?><span class="badge bg-green-lt">BASE</span><?php else: ?><span class="text-secondary">—</span><?php endif; ?></td>
<td><?php if($u->is_active): ?><span class="badge bg-green-lt">AKTIF</span><?php else: ?><span class="badge bg-secondary-lt">NONAKTIF</span><?php endif; ?></td>
<td class="small text-secondary">
<?php $__currentLoopData = $u->conversionsFrom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>1 <?php echo e($u->symbol); ?> = <?php echo e(rtrim(rtrim(number_format($c->factor, 6, '.', ''), '0'), '.')); ?> <?php echo e($c->toUnit->symbol); ?>

<form method="POST" action="<?php echo e(route('unit-conversions.destroy', $c)); ?>" class="d-inline" onsubmit="return confirm('Hapus konversi?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-ghost-danger py-0" type="submit">×</button></form>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<form method="POST" action="<?php echo e(route('units.conversions.store', $u)); ?>" class="d-flex gap-1 mt-1"><?php echo csrf_field(); ?>
<select name="to_unit_id" class="form-select form-select-sm" required><option value="">→ satuan</option><?php $__currentLoopData = \App\Models\Unit::where('id', '!=', $u->id)->where('is_active', true)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($t->id); ?>"><?php echo e($t->symbol); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
<input name="factor" type="number" step="0.000001" min="0.000001" class="form-control form-control-sm" style="width:110px" placeholder="faktor" required/>
<button class="btn btn-sm btn-white" type="submit">+</button>
</form>
</td>
<td class="text-end">
<form method="POST" action="<?php echo e(route('units.destroy', $u)); ?>" class="d-inline" onsubmit="return confirm('Hapus satuan?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="8"><?php if (isset($component)) { $__componentOriginal4f22a152e0729cd34293e65bd200d933 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f22a152e0729cd34293e65bd200d933 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty','data' => ['title' => 'Belum ada satuan']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Belum ada satuan']); ?>
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
<div class="mt-3"><?php echo e($units->links()); ?></div>
</div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah / ubah satuan</h3></div>
<div class="card-body">
<form method="POST" action="<?php echo e(route('units.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-2">
<div class="col-4"><label class="form-label">Kode *</label><input name="code" class="form-control" required placeholder="cth. SAK"/></div>
<div class="col-8"><label class="form-label">Nama *</label><input name="name" class="form-control" required placeholder="cth. Karung 50kg"/></div>
<div class="col-4"><label class="form-label">Simbol *</label><input name="symbol" class="form-control" required placeholder="sak"/></div>
<div class="col-8"><label class="form-label">Tipe *</label>
<select name="unit_type" class="form-select"><?php $__currentLoopData = ['WEIGHT','VOLUME','COUNT','LENGTH']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($t); ?>"><?php echo e($t); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
</div>
<button class="btn btn-primary mt-2" type="submit">Tambah satuan</button>
</form>
<hr/>
<p class="text-secondary small">Ubah nama/simbol satuan existing:</p>
<form method="POST" action="#" onsubmit="return updateUnit(event)"><?php echo csrf_field(); ?>
<div class="row g-2">
<div class="col-12"><select id="unit-edit" class="form-select"><?php $__currentLoopData = \App\Models\Unit::orderBy('code')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($u->id); ?>" data-name="<?php echo e($u->name); ?>" data-symbol="<?php echo e($u->symbol); ?>" data-type="<?php echo e($u->unit_type); ?>" data-active="<?php echo e($u->is_active ? 1 : 0); ?>"><?php echo e($u->code); ?> — <?php echo e($u->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-6"><input id="unit-name" class="form-control" placeholder="Nama"/></div>
<div class="col-3"><input id="unit-symbol" class="form-control" placeholder="Simbol"/></div>
<div class="col-3"><select id="unit-active" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
</div>
<button class="btn btn-white mt-2" type="submit">Simpan perubahan</button>
</form>
</div></div>
</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
const sel = document.getElementById('unit-edit');
function syncEdit() {
const o = sel.options[sel.selectedIndex];
document.getElementById('unit-name').value = o.dataset.name;
document.getElementById('unit-symbol').value = o.dataset.symbol;
document.getElementById('unit-active').value = o.dataset.active;
}
sel.addEventListener('change', syncEdit); syncEdit();
function updateUnit(e) {
e.preventDefault();
const id = sel.value;
const form = document.createElement('form');
form.method = 'POST';
form.action = `/units/${id}`;
form.innerHTML = `<?php echo csrf_field(); ?><input type="hidden" name="_method" value="PUT"/><input type="hidden" name="name" value="${document.getElementById('unit-name').value}"/><input type="hidden" name="symbol" value="${document.getElementById('unit-symbol').value}"/><input type="hidden" name="unit_type" value="${sel.options[sel.selectedIndex].dataset.type}"/><input type="hidden" name="is_active" value="${document.getElementById('unit-active').value}"/>`;
document.body.appendChild(form);
form.submit();
return false;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/units/index.blade.php ENDPATH**/ ?>
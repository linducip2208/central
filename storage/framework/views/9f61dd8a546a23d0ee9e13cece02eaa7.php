<?php $__env->startSection('title', 'Buat Purchase Order'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('purchase-orders.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Supplier *</label>
<select name="supplier_id" class="form-select" required><option value="">—</option><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Dari PR (opsional)</label>
<select name="purchase_request_id" class="form-select"><option value="">— manual —</option><?php $__currentLoopData = $prs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($pr->id); ?>"><?php echo e($pr->number); ?> (<?php echo e($pr->items->count()); ?> item)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-2"><label class="form-label">Ekspektasi tiba</label><input name="expected_date" type="date" class="form-control"/></div>
<div class="col-md-2"><label class="form-label">Pembayaran *</label>
<select name="payment_terms" class="form-select"><?php $__currentLoopData = ['CASH','CREDIT','COD']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($t); ?>"><?php echo e($t); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
</div>
<h4 class="mt-4">Item * <span class="text-secondary small">qty dalam satuan dasar bahan</span></h4>
<div class="table-responsive"><table class="table" id="po-table">
<thead><tr><th>Bahan (kode)</th><th style="width:140px">Qty</th><th style="width:170px">Harga satuan</th><th style="width:60px"></th></tr></thead>
<tbody>
<tr>
<td><select name="items[0][ingredient_id]" class="form-select" required><option value="">—</option><?php $__currentLoopData = \App\Models\Ingredient::active()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->id); ?>"><?php echo e($i->code); ?> — <?php echo e($i->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></td>
<td><input name="items[0][qty]" type="number" step="0.001" min="0.001" class="form-control" required/></td>
<td><input name="items[0][price]" type="number" step="0.01" min="0" class="form-control" required/></td>
<td></td>
</tr>
</tbody>
</table></div>
<button type="button" class="btn btn-white btn-sm" onclick="addPo()">+ Tambah item</button>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Simpan draft PO</button><a href="<?php echo e(route('purchase-orders.index')); ?>" class="btn btn-white">Batal</a></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
let poi = 1;
function addPo() {
document.querySelector('#po-table tbody').insertAdjacentHTML('beforeend', `<tr><td><select name="items[${poi}][ingredient_id]" class="form-select"><?php $__currentLoopData = \App\Models\Ingredient::active()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($i->id); ?>"><?php echo e($i->code); ?> — <?php echo e($i->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></td><td><input name="items[${poi}][qty]" type="number" step="0.001" min="0.001" class="form-control"/></td><td><input name="items[${poi}][price]" type="number" step="0.01" min="0" class="form-control"/></td><td><button type="button" class="btn btn-sm btn-ghost-danger" onclick="this.closest('tr').remove()">×</button></td></tr>`);
poi++;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/purchase-orders/form.blade.php ENDPATH**/ ?>
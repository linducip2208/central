<?php $__env->startSection('title', 'Reservasi Stok'); ?>
<?php $__env->startSection('subtitle', 'Stok yang direservasi tidak bisa dikonsumsi sampai dilepas — untuk alokasi delivery/produksi'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('inventory.reserve')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-3"><label class="form-label">Gudang *</label>
<select name="warehouse_id" class="form-select" required><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-3"><label class="form-label">Tipe *</label>
<select name="item_type" id="item-type" class="form-select" onchange="syncItems()"><option value="ingredient">Bahan baku</option><option value="product">Produk jadi</option></select></div>
<div class="col-md-3"><label class="form-label">Item *</label>
<select name="item_id" id="item-id" class="form-select" required></select></div>
<div class="col-md-3"><label class="form-label">Qty *</label><input name="qty" type="number" step="0.001" min="0.001" class="form-control" required/></div>
</div>
<div class="form-footer mt-3 d-flex gap-2">
<button class="btn btn-primary" type="submit">Reservasi</button>
<button class="btn btn-white" type="submit" formaction="<?php echo e(route('inventory.release')); ?>" onclick="return confirm('Lepas reservasi sejumlah ini?')">Lepas reservasi</button>
</div>
</form>
</div></div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
const ING = <?php echo json_encode($ingredients->map(fn($i) => ['id' => $i->id, 'name' => $i->name]), 512) ?>;
const PRD = <?php echo json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]), 512) ?>;
function syncItems() {
const t = document.getElementById('item-type').value;
const sel = document.getElementById('item-id');
const list = t === 'product' ? PRD : ING;
sel.innerHTML = list.map(o => `<option value="${o.id}">${o.name}</option>`).join('');
}
syncItems();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/inventory/reserve.blade.php ENDPATH**/ ?>
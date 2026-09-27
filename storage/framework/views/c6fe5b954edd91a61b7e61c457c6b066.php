<?php $__env->startSection('title', 'Terima Barang (Goods Receipt)'); ?>
<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
<form method="POST" action="<?php echo e(route('goods-receipts.store')); ?>"><?php echo csrf_field(); ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label">Purchase order *</label>
<select name="purchase_order_id" id="po-select" class="form-select" required onchange="fillPo()"><option value="">— pilih PO —</option>
<?php $__currentLoopData = $pos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($po->id); ?>" data-items='<?php echo $po->items->map(fn($i) => ['id' => $i->id, 'name' => $i->ingredient->name, 'sisa' => $i->remainingToReceive()])->toJson(); ?>' data-warehouse="<?php echo e($po->warehouse_id); ?>"><?php echo e($po->number); ?> — <?php echo e($po->supplier->name); ?> (<?php echo e($po->status); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">Gudang tujuan *</label>
<select name="warehouse_id" id="wh-select" class="form-select" required><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
<div class="col-md-4"><label class="form-label">No. surat jalan</label><input name="delivery_note_no" class="form-control"/></div>
<div class="col-md-12"><label class="form-label">Catatan</label><input name="notes" class="form-control"/></div>
</div>
<h4 class="mt-4">Item diterima *</h4>
<div id="gr-items" class="row g-2"></div>
<p class="text-secondary small mt-2">Qty tidak boleh melebihi sisa PO. Batch & expired wajib untuk bahan mudah rusak.</p>
<div class="form-footer mt-3 d-flex gap-2"><button class="btn btn-success" type="submit">Posting ke stok</button><a href="<?php echo e(route('goods-receipts.index')); ?>" class="btn btn-white">Batal</a></div>
</form>
</div></div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
function fillPo() {
const sel = document.getElementById('po-select');
const opt = sel.options[sel.selectedIndex];
const wrap = document.getElementById('gr-items');
wrap.innerHTML = '';
if (!opt || !opt.dataset.items) return;
if (opt.dataset.warehouse) document.getElementById('wh-select').value = opt.dataset.warehouse;
const items = JSON.parse(opt.dataset.items);
items.forEach((it, idx) => {
wrap.insertAdjacentHTML('beforeend', `<div class="col-12"><div class="card card-sm"><div class="card-body"><div class="row g-2 align-items-end">
<input type="hidden" name="items[${idx}][po_item_id]" value="${it.id}"/>
<div class="col-md-4"><label class="form-label">${it.name} (sisa ${it.sisa})</label><input name="items[${idx}][qty]" type="number" step="0.001" min="0.001" max="${it.sisa}" value="${it.sisa}" class="form-control" required/></div>
<div class="col-md-1"><label class="form-label">Reject</label><input name="items[${idx}][rejected]" type="number" step="0.001" min="0" value="0" class="form-control"/></div>
<div class="col-md-3"><label class="form-label">No. batch</label><input name="items[${idx}][batch_no]" class="form-control" placeholder="otomatis bila kosong"/></div>
<div class="col-md-4"><label class="form-label">Expired</label><input name="items[${idx}][expiry_date]" type="date" class="form-control"/></div>
</div></div></div></div>`);
});
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\centralkitchen\resources\views/goods-receipts/form.blade.php ENDPATH**/ ?>
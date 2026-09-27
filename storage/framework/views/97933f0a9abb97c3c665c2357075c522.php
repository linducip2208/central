<div class="empty">
<div class="empty-icon"><i class="ti ti-<?php echo e($icon ?? 'inbox'); ?> fs-1 text-secondary"></i></div>
<p class="empty-title"><?php echo e($title ?? 'Belum ada data'); ?></p>
<p class="empty-subtitle text-secondary"><?php echo e($subtitle ?? 'Data akan tampil di sini setelah ditambahkan.'); ?></p>
<?php if(isset($action)): ?>
<div class="empty-action"><?php echo e($action); ?></div>
<?php endif; ?>
</div>
<?php /**PATH D:\project laravel\centralkitchen\resources\views/components/empty.blade.php ENDPATH**/ ?>
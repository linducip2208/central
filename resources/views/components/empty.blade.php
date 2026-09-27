<div class="empty">
<div class="empty-icon"><i class="ti ti-{{ $icon ?? 'inbox' }} fs-1 text-secondary"></i></div>
<p class="empty-title">{{ $title ?? 'Belum ada data' }}</p>
<p class="empty-subtitle text-secondary">{{ $subtitle ?? 'Data akan tampil di sini setelah ditambahkan.' }}</p>
@if(isset($action))
<div class="empty-action">{{ $action }}</div>
@endif
</div>

@extends('layouts.app')
@section('title', 'Template Notifikasi')
@section('content')
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Tipe</th><th>Subject</th><th>Body</th><th>Email</th></tr></thead>
<tbody>
@forelse($templates as $t)
<tr><td><code>{{ $t->type }}</code></td><td>{{ $t->subject }}</td><td class="text-secondary small">{{ \Illuminate\Support\Str::limit($t->body, 120) }}</td><td>@if($t->mail_enabled)<span class="badge bg-green-lt">ON</span>@else<span class="badge bg-secondary-lt">OFF</span>@endif</td></tr>
@empty<tr><td colspan="4" class="text-center text-secondary py-3">Belum ada template. Template dibuat otomatis saat event pertama atau tambah manual di bawah.</td></tr>@endforelse
</tbody></table></div>
</div>
<div class="card-body border-top">
<h4>Tambah / ubah template</h4>
<form method="POST" action="{{ route('notifications.templates.store') }}">@csrf
<div class="row g-2">
<div class="col-md-3"><input name="type" class="form-control" placeholder="tipe * (cth. pr_approved)" required/></div>
<div class="col-md-3"><input name="subject" class="form-control" placeholder="subject *" required/></div>
<div class="col-md-4"><input name="body" class="form-control" placeholder="body, pakai @{{nama}} *" required/></div>
<div class="col-md-2"><label class="form-check mt-2"><input type="checkbox" name="mail_enabled" value="1" class="form-check-input"/><span class="form-check-label">Email</span></label></div>
</div>
<button class="btn btn-primary mt-2" type="submit">Simpan</button>
</form>
</div></div>
@endsection

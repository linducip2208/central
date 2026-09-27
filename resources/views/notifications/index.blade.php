@extends('layouts.app')
@section('title', 'Notifikasi')
@section('actions')
<form method="POST" action="{{ route('notifications.read-all') }}" class="d-inline">@csrf<button class="btn btn-white" type="submit">Tandai semua dibaca</button></form>
@endsection
@section('content')
<div class="card"><div class="list-group list-group-flush">
@forelse($notifications as $n)
<div class="list-group-item {{ $n->read_at ? '' : 'bg-blue-lt' }}">
<div class="d-flex justify-content-between align-items-center">
<div><span class="badge bg-blue-lt me-2">{{ $n->data['type'] ?? '-' }}</span><span class="small">{{ $n->created_at->diffForHumans() }}</span>
<div class="text-secondary small">{{ json_encode($n->data['data'] ?? []) }}</div></div>
@if(!$n->read_at)
<form method="POST" action="{{ route('notifications.read', $n->id) }}">@csrf<button class="btn btn-sm btn-white" type="submit">Tandai dibaca</button></form>
@endif
</div>
</div>
@empty<div class="list-group-item text-secondary">Tidak ada notifikasi.</div>@endforelse
</div></div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection

@extends('layouts.app')
@section('title', 'Approval Inbox')
@section('subtitle', 'Jejak keputusan reusable approval engine (siapa, kapan, komentar)')
@section('content')
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Waktu</th><th>Entitas</th><th>Aksi</th><th>Level</th><th>Status</th><th>Komentar</th><th>Diputus oleh</th></tr></thead>
<tbody>
@forelse($approvals as $a)
<tr>
<td class="text-secondary">{{ $a->decided_at?->format('d M Y H:i') ?? $a->created_at->format('d M Y H:i') }}</td>
<td class="text-secondary">{{ class_basename($a->approvable_type) }} · {{ $a->ref_label }}</td>
<td>{{ $a->action }}</td>
<td class="text-end">L{{ $a->level }}</td>
<td>@if($a->status === 'APPROVED')<span class="badge bg-green-lt">APPROVED</span>@else<span class="badge bg-red-lt">REJECTED</span>@endif</td>
<td class="text-secondary">{{ $a->comment ?? '—' }}</td>
<td>{{ $a->decider->name ?? '—' }}</td>
</tr>
@empty<tr><td colspan="7"><x-empty title="Belum ada keputusan"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $approvals->links() }}</div>
</div></div>
@endsection

@extends('layouts.app')
@section('title', 'Sekolah')
@section('actions')<a href="{{ route('schools.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Sekolah</a>@endsection
@section('content')
<div class="card"><div class="card-body">
<x-filter :statuses="['ACTIVE','INACTIVE']"/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Jenjang</th><th>Dapur</th><th class="text-end">Siswa</th><th class="text-end">Target porsi</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse($schools as $s)
<tr>
<td class="text-secondary">{{ $s->code }}</td>
<td><a href="{{ route('schools.show', $s) }}">{{ $s->name }}</a><div class="text-secondary small">{{ $s->district ?? '' }} {{ $s->city ?? '' }}</div></td>
<td>{{ $s->level }}</td>
<td class="text-secondary">{{ $s->centralKitchen->name ?? '-' }}</td>
<td class="text-end">{{ number_format($s->student_count) }}</td>
<td class="text-end">{{ number_format($s->target_portions) }}</td>
<td><x-badge :status="$s->status"/></td>
<td class="text-end"><a class="btn btn-sm btn-white" href="{{ route('schools.show', $s) }}">Detail</a></td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada sekolah"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $schools->links() }}</div>
</div></div>
@endsection

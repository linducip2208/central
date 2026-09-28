@extends('layouts.app')
@section('title', $document->title)
@section('subtitle', $document->code . ' · ' . $document->category . ' v' . $document->version)
@section('actions')
@if($document->status === 'DRAFT')
<form method="POST" action="{{ route('documents.submit', $document) }}" class="d-inline">@csrf<button class="btn btn-primary" type="submit">Kirim review</button></form>
@endif
@if(in_array($document->status, ['REVIEW','DRAFT']))
<form method="POST" action="{{ route('documents.approve', $document) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Setujui</button></form>
@endif
@if($document->status === 'APPROVED')
<form method="POST" action="{{ route('documents.publish', $document) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Publikasikan</button></form>
@endif
@if(!in_array($document->status, ['ARCHIVED']))
<form method="POST" action="{{ route('documents.archive', $document) }}" class="d-inline">@csrf<button class="btn btn-ghost-danger" type="submit" onclick="return confirm('Arsipkan?')">Arsip</button></form>
@endif
@if($document->isPublished() && !$acked)
<form method="POST" action="{{ route('documents.ack', $document) }}" class="d-inline">@csrf<button class="btn btn-white" type="submit">Saya sudah membaca</button></form>
@endif
@endsection
@section('content')
<div class="card"><div class="card-body">
<div class="mb-2"><x-badge :status="$document->status"/> @if($acked)<span class="badge bg-green-lt ms-1">SUDAH DIBACA</span>@endif</div>
<div style="white-space:pre-wrap">{{ $document->content }}</div>
@if($document->attachment_path)
<div class="mt-3"><a href="{{ Storage::url($document->attachment_path) }}" target="_blank" class="btn btn-white">Unduh lampiran</a></div>
@endif
<p class="text-secondary small mt-3 mb-0">Berlaku: {{ $document->effective_from ?? '—' }} s.d. {{ $document->expires_at ?? '—' }} · Diakui {{ $document->acknowledgements()->count() }} pengguna</p>
</div></div>
@endsection

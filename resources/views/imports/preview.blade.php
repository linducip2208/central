@extends('layouts.app')
@section('title', 'Preview Import ' . $entity)
@section('content')
<div class="row g-3">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<dl class="row">
<dt class="col-6">Total baris</dt><dd class="col-6">{{ $result['total'] }}</dd>
<dt class="col-6">Valid</dt><dd class="col-6 text-green">{{ $result['valid'] }}</dd>
<dt class="col-6">Error</dt><dd class="col-6 text-red">{{ $result['error_count'] }}</dd>
</dl>
@if($result['error_count'])
<div class="alert alert-warning"><ul class="mb-0">@foreach($result['errors'] as $e)<li class="small">{{ $e }}</li>@endforeach</ul></div>
@endif
@if($result['valid'] > 0)
<form method="POST" action="{{ route('imports.commit', ['entity' => $entity]) }}">@csrf
<input type="hidden" name="tmp" value="{{ $tmp }}"/>
<button class="btn btn-success w-100" type="submit">Commit {{ $result['valid'] }} baris valid</button>
</form>
@endif
<a href="{{ route('imports.index') }}" class="btn btn-ghost-secondary w-100 mt-2">Batal</a>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-header"><h3 class="card-title">Contoh 10 baris pertama</h3></div>
<div class="table-responsive"><table class="table table-sm table-vcenter card-table">
<thead><tr>@foreach(array_keys($result['rows'][0] ?? []) as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
<tbody>
@foreach($result['rows'] as $r)
<tr>@foreach($r as $v)<td class="small">{{ \Illuminate\Support\Str::limit($v, 30) }}</td>@endforeach</tr>
@endforeach
</tbody></table></div></div>
</div>
</div>
@endsection

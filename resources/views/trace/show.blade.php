@extends('layouts.app')
@section('title', 'Genealogy ' . $batch->batch_no)
@section('actions')<a href="{{ route('recalls.create') }}" class="btn btn-danger">Buat recall dari batch ini</a>@endsection
@section('content')
<div class="card mb-3"><div class="card-body">
<h3>{{ $batch->batch_no }} <span class="ms-2"><x-badge :status="$batch->status"/></span></h3>
<p class="text-secondary mb-0">{{ $itemName ?? $batch->item_type.' #'.$batch->item_id }} · {{ $batch->warehouse->name ?? '' }} · sisa {{ number_format($batch->remaining_qty, 2) }} · supplier: {{ $batch->supplier->name ?? '—' }}</p>
</div></div>
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">← Backward (asal-usul)</h3></div>
<div class="card-body">
@if($backward['production'])
<div class="mb-2"><strong>Produksi:</strong> {{ $backward['production']['number'] }} ({{ $backward['production']['product'] }}, {{ $backward['production']['date'] }})</div>
<h4>Bahan yang dipakai:</h4>
<ul>
@forelse($backward['ingredient_batches'] as $ib)
<li><a href="{{ route('trace.batch', $ib['id']) }}">{{ $ib['batch_no'] }}</a> — sisa {{ number_format($ib['remaining_qty'], 2) }} @if(isset($ib['goods_receipt'])) (GR {{ $ib['goods_receipt']['number'] }} · {{ $ib['goods_receipt']['supplier'] }}) @endif</li>
@empty<li class="text-secondary">Tidak ada jejak konsumsi.</li>@endforelse
</ul>
@elseif(isset($backward['goods_receipt']))
<div><strong>Penerimaan:</strong> GR {{ $backward['goods_receipt']['number'] }} · {{ $backward['goods_receipt']['supplier'] }}</div>
@else
<p class="text-secondary">Tidak ada jejak hulu (batch awal/stok awal).</p>
@endif
</div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">Forward (dampak) →</h3></div>
<div class="card-body">
@if($forward['goods_receipt'])
<div class="mb-2"><strong>Penerimaan:</strong> GR {{ $forward['goods_receipt']['number'] }} · {{ $forward['goods_receipt']['supplier'] }}</div>
@endif
@forelse($forward['productions'] as $p)
<div class="mb-2 border rounded p-2">
<div><strong>WO {{ $p['number'] }}</strong> ({{ $p['product'] }}) — {{ $p['status'] }}</div>
<ul class="mb-0">
@foreach($p['finished_batches'] as $fb)
<li><a href="{{ route('trace.batch', $fb['id']) }}">{{ $fb['batch_no'] }}</a> — sisa {{ number_format($fb['remaining_qty'], 2) }}
<ul>
@foreach($fb['deliveries'] as $d)
<li>🚚 {{ $d['number'] }} → {{ $d['school'] }} ({{ $d['status'] }}, {{ $d['date'] }})</li>
@endforeach
</ul>
</li>
@endforeach
</ul>
</div>
@empty
@if(!empty($forward['deliveries']))
<ul>
@foreach($forward['deliveries'] as $d)
<li>🚚 {{ $d['number'] }} → {{ $d['school'] }} ({{ $d['status'] }}, {{ $d['date'] }})</li>
@endforeach
</ul>
@else
<p class="text-secondary">Belum ada dampak hilir tercatat.</p>
@endif
@endforelse
</div></div>
</div>
</div>
@endsection

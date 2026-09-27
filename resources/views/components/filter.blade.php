<form method="GET" class="row g-2 mb-3">
<div class="col-md-4">
<div class="input-icon">
<span class="input-icon-addon"><i class="ti ti-search"></i></span>
<input type="text" name="q" class="form-control" placeholder="Cari…" value="{{ request('q') }}"/>
</div>
</div>
@if(!empty($statuses))
<div class="col-md-3">
<select name="status" class="form-select" onchange="this.form.submit()">
<option value="">— Semua status —</option>
@foreach($statuses as $s)
<option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
@endforeach
</select>
</div>
@endif
<div class="col-md-auto">
<button class="btn btn-white" type="submit">Filter</button>
@if(request('q') || request('status'))
<a href="{{ url()->current() }}" class="btn btn-ghost-secondary">Reset</a>
@endif
</div>
</form>

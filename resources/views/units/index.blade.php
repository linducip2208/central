@extends('layouts.app')
@section('title', 'Satuan & Konversi')
@section('subtitle', 'Satuan dasar dipakai bahan/produk; konversi dipakai katering saat terima/consumption beda kemasan')
@section('content')
<div class="row g-3">
<div class="col-lg-7">
<div class="card"><div class="card-body">
<x-filter/>
<div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Kode</th><th>Nama</th><th>Simbol</th><th>Tipe</th><th>Base</th><th>Aktif</th><th>Konversi</th><th></th></tr></thead>
<tbody>
@forelse($units as $u)
<tr>
<td class="fw-bold">{{ $u->code }}</td>
<td>{{ $u->name }}</td>
<td>{{ $u->symbol }}</td>
<td class="text-secondary">{{ $u->unit_type }}</td>
<td>@if($u->is_base)<span class="badge bg-green-lt">BASE</span>@else<span class="text-secondary">—</span>@endif</td>
<td>@if($u->is_active)<span class="badge bg-green-lt">AKTIF</span>@else<span class="badge bg-secondary-lt">NONAKTIF</span>@endif</td>
<td class="small text-secondary">
@foreach($u->conversionsFrom as $c)
<div>1 {{ $u->symbol }} = {{ rtrim(rtrim(number_format($c->factor, 6, '.', ''), '0'), '.') }} {{ $c->toUnit->symbol }}
<form method="POST" action="{{ route('unit-conversions.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Hapus konversi?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger py-0" type="submit">×</button></form>
</div>
@endforeach
<form method="POST" action="{{ route('units.conversions.store', $u) }}" class="d-flex gap-1 mt-1">@csrf
<select name="to_unit_id" class="form-select form-select-sm" required><option value="">→ satuan</option>@foreach(\App\Models\Unit::where('id', '!=', $u->id)->where('is_active', true)->get() as $t)<option value="{{ $t->id }}">{{ $t->symbol }}</option>@endforeach</select>
<input name="factor" type="number" step="0.000001" min="0.000001" class="form-control form-control-sm" style="width:110px" placeholder="faktor" required/>
<button class="btn btn-sm btn-white" type="submit">+</button>
</form>
</td>
<td class="text-end">
<form method="POST" action="{{ route('units.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Hapus satuan?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost-danger" type="submit"><i class="ti ti-trash"></i></button></form>
</td>
</tr>
@empty<tr><td colspan="8"><x-empty title="Belum ada satuan"/></td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $units->links() }}</div>
</div></div>
</div>
<div class="col-lg-5">
<div class="card"><div class="card-header"><h3 class="card-title">Tambah / ubah satuan</h3></div>
<div class="card-body">
<form method="POST" action="{{ route('units.store') }}">@csrf
<div class="row g-2">
<div class="col-4"><label class="form-label">Kode *</label><input name="code" class="form-control" required placeholder="cth. SAK"/></div>
<div class="col-8"><label class="form-label">Nama *</label><input name="name" class="form-control" required placeholder="cth. Karung 50kg"/></div>
<div class="col-4"><label class="form-label">Simbol *</label><input name="symbol" class="form-control" required placeholder="sak"/></div>
<div class="col-8"><label class="form-label">Tipe *</label>
<select name="unit_type" class="form-select">@foreach(['WEIGHT','VOLUME','COUNT','LENGTH'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach</select></div>
</div>
<button class="btn btn-primary mt-2" type="submit">Tambah satuan</button>
</form>
<hr/>
<p class="text-secondary small">Ubah nama/simbol satuan existing:</p>
<form method="POST" action="#" onsubmit="return updateUnit(event)">@csrf
<div class="row g-2">
<div class="col-12"><select id="unit-edit" class="form-select">@foreach(\App\Models\Unit::orderBy('code')->get() as $u)<option value="{{ $u->id }}" data-name="{{ $u->name }}" data-symbol="{{ $u->symbol }}" data-type="{{ $u->unit_type }}" data-active="{{ $u->is_active ? 1 : 0 }}">{{ $u->code }} — {{ $u->name }}</option>@endforeach</select></div>
<div class="col-6"><input id="unit-name" class="form-control" placeholder="Nama"/></div>
<div class="col-3"><input id="unit-symbol" class="form-control" placeholder="Simbol"/></div>
<div class="col-3"><select id="unit-active" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
</div>
<button class="btn btn-white mt-2" type="submit">Simpan perubahan</button>
</form>
</div></div>
</div>
</div>
@endsection
@push('scripts')
<script>
const sel = document.getElementById('unit-edit');
function syncEdit() {
const o = sel.options[sel.selectedIndex];
document.getElementById('unit-name').value = o.dataset.name;
document.getElementById('unit-symbol').value = o.dataset.symbol;
document.getElementById('unit-active').value = o.dataset.active;
}
sel.addEventListener('change', syncEdit); syncEdit();
function updateUnit(e) {
e.preventDefault();
const id = sel.value;
const form = document.createElement('form');
form.method = 'POST';
form.action = `/units/${id}`;
form.innerHTML = `@csrf<input type="hidden" name="_method" value="PUT"/><input type="hidden" name="name" value="${document.getElementById('unit-name').value}"/><input type="hidden" name="symbol" value="${document.getElementById('unit-symbol').value}"/><input type="hidden" name="unit_type" value="${sel.options[sel.selectedIndex].dataset.type}"/><input type="hidden" name="is_active" value="${document.getElementById('unit-active').value}"/>`;
document.body.appendChild(form);
form.submit();
return false;
}
</script>
@endpush

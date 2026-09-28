<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Panduan — {{ config('app.name') }}</title>
<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/css/tabler.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/@tabler/icons@3.28.1/tabler-icons.min.css" rel="stylesheet"/>
</head>
<body>
<div class="page">
<header class="navbar navbar-expand-md d-print-none">
<div class="container-xl">
<h1 class="navbar-brand"><a href="{{ route('docs') }}" class="text-decoration-none"><i class="ti ti-chef-hat me-1"></i>MBG Central Kitchen <span class="badge bg-green-lt ms-1">Docs</span></a></h1>
<div class="navbar-nav flex-row order-md-last ms-auto">
<a href="{{ route('docs', ['s' => 'tutorial']) }}" class="nav-link @if($section === 'tutorial') active fw-bold @endif">Tutorial</a>
<a href="{{ route('docs', ['s' => 'akses']) }}" class="nav-link @if($section === 'akses') active fw-bold @endif">Hak Akses</a>
<a href="{{ route('docs', ['s' => 'password']) }}" class="nav-link @if($section === 'password') active fw-bold @endif">Password</a>
<a href="{{ route('login') }}" class="btn btn-primary ms-2">Masuk aplikasi</a>
</div>
</div>
</header>
<div class="page-wrapper"><div class="page-body"><div class="container-xl">

@if($section === 'tutorial')
<h2 class="page-title mb-3">Cara memakai aplikasi</h2>
<div class="row g-3">
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">1. Masuk (login)</h3></div>
<div class="card-body"><ol class="mb-0">
<li>Buka halaman <a href="{{ route('login') }}">login</a>.</li>
<li>Masukkan email + password akun demo (lihat tab <a href="{{ route('docs', ['s' => 'password']) }}">Password</a>).</li>
<li>Jika akun memakai 2FA, masukkan kode 6 digit dari aplikasi authenticator.</li>
<li>Anda masuk ke <strong>Dashboard</strong>: ringkasan porsi, PO aktif, delivery, stok menipis, batch hampir expired.</li>
</ol></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">2. Alur kerja utama (wajib berurutan)</h3></div>
<div class="card-body"><ol class="mb-0">
<li><strong>Demand</strong>: catat kebutuhan porsi per sekolah/tanggal.</li>
<li><strong>Generate PR</strong>: kebutuhan bahan dihitung otomatis dari BOM/resep.</li>
<li><strong>PR → submit → approve</strong> oleh peran yang berwenang.</li>
<li><strong>PO</strong>: pilih supplier (atau via RFQ untuk perbandingan harga) → submit → approve.</li>
<li><strong>Goods Receipt</strong>: terima barang (boleh parsial) → stok + batch otomatis terbentuk.</li>
<li><strong>Produksi</strong>: Rencana → generate WO → rilis → material check → konsumsi (FEFO) → selesai → hasil masuk stok.</li>
<li><strong>QC</strong>: uji organoleptik/suhu; gagal → NCR + karantina batch.</li>
<li><strong>Packaging → Distribusi → Delivery</strong>: kemas, alokasi sekolah, berangkatkan, serah terima + foto bukti.</li>
<li><strong>Sekolah</strong>: konfirmasi terima/tolak + kehadiran via Portal.</li>
<li><strong>Laporan</strong>: stok, produksi, delivery, keuangan, waste di menu Reports/Analytics.</li>
</ol></div></div>
</div>
<div class="col-lg-6">
<div class="card"><div class="card-header"><h3 class="card-title">3. Aturan penting (jangan dilanggar)</h3></div>
<div class="card-body"><ul class="mb-0">
<li>Stok <strong>tidak bisa minus</strong>: konsumsi melebihi stok ditolak sistem.</li>
<li>Batch <strong>expired / dikarantina tidak ikut</strong> konsumsi (FEFO otomatis).</li>
<li>Setiap mutasi tercatat di <strong>ledger</strong> (menu Inventory → Ledger mutasi) — tidak bisa dihapus/diubah.</li>
<li>Selisih stok diselesaikan via <strong>Stock Opname</strong> (bukan ubah angka langsung).</li>
<li>Transaksi yang sudah approve/diposting <strong>tidak bisa dihapus</strong>.</li>
<li>Data antar organisasi <strong>terisolasi</strong>: Anda hanya melihat data organisasi sendiri.</li>
</ul></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">4. Fitur pendukung</h3></div>
<div class="card-body"><ul class="mb-0">
<li><strong>Scan barcode</strong> (menu WMS → Scan): tempel pemindai untuk cari bin/batch/item.</li>
<li><strong>Traceability</strong>: lacak asal-usul batch (maju/mundur) + recall.</li>
<li><strong>MRP</strong>: hitung kebutuhan bahan dari demand plan (expand Demand → MRP).</li>
<li><strong>Notifikasi</strong>: ikon lonceng di navbar; atur preferensi di Profil.</li>
<li><strong>Profil</strong>: ganti password, aktifkan 2FA, buat/cabut token API.</li>
<li><strong>Cetak</strong>: tombol Cetak di laporan memakai fitur print browser.</li>
</ul></div></div>
</div>
</div>
@endif

@if($section === 'akses')
<h2 class="page-title mb-3">Hak akses pengguna</h2>
<p class="text-secondary">Setiap peran hanya membuka menu sesuai izinnya. <code>super-admin</code>/<code>admin</code> membuka semua menu. Tanda ✓ = diizinkan.</p>
<div class="card"><div class="table-responsive"><table class="table table-vcenter card-table table-sm">
<thead><tr><th>Izin</th>@foreach($roles as $r)<th class="text-center">{{ $r }}</th>@endforeach</tr></thead>
<tbody>
@foreach($permissions as $p)
<tr><td><code>{{ $p }}</code></td>
@foreach($roles as $r)
@php $perms = $matrix[$r] ?? []; $ok = $r === 'super-admin' || $r === 'admin' || in_array($p, $perms === '*' ? [] : $perms); @endphp
<td class="text-center">@if($ok)<span class="text-green">✓</span>@else<span class="text-secondary">—</span>@endif</td>
@endforeach
</tr>
@endforeach
</tbody></table></div></div>
<p class="text-secondary small">Catatan: <code>admin</code> mendapat semua izin lewat bypass sistem. Penugasan peran dilakukan admin di menu Users. Persetujuan nominal besar dapat diatur tambahan di Approvals → Matriks.</p>
@endif

@if($section === 'password')
<h2 class="page-title mb-3">Akun demo & password</h2>
<div class="alert alert-warning"><strong>Penting:</strong> akun di bawah hanya untuk demo/pelatihan. Di server produksi, <strong>wajib diganti</strong> lewat menu Profil → Ganti password, dan aktifkan 2FA untuk akun admin.</div>
<div class="card"><div class="table-responsive"><table class="table table-vcenter card-table">
<thead><tr><th>Peran</th><th>Email</th><th>Password demo</th><th>Kegunaan</th></tr></thead>
<tbody>
@foreach($accounts as [$role, $email, $use])
<tr><td>{{ $role }}</td><td><code>{{ $email }}</code></td><td><code>password123</code></td><td class="text-secondary">{{ $use }}</td></tr>
@endforeach
</tbody></table></div></div>
<div class="card mt-3"><div class="card-header"><h3 class="card-title">Aturan password</h3></div>
<div class="card-body"><ul class="mb-0">
<li>Minimal <strong>8 karakter</strong> saat ganti password.</li>
<li>Ganti berkala lewat <strong>Profil → Ganti password</strong> (perlu password lama).</li>
<li>Aktifkan <strong>2FA (TOTP)</strong>: Profil → pindai kode ke Google/Microsoft Authenticator → verifikasi 6 digit. Setelah aktif, setiap login meminta kode.</li>
<li>Jangan bagikan password. Admin dapat menonaktifkan akun (Users → Nonaktifkan) bila ada penyalahgunaan.</li>
<li>Login salah berulang dibatasi otomatis (throttle); tunggu sebentar lalu coba lagi.</li>
</ul></div></div>
@endif

</div></div>
<footer class="footer footer-transparent"><div class="container-xl"><div class="text-center text-secondary">{{ config('app.name') }} v{{ config('mbg.version') }}</div></div></footer>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js"></script>
</body>
</html>

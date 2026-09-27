# MBG Central Kitchen — Dokumentasi

Sistem ERP operasional dapur program Makan Bergizi Gratis (MBG).
Stack: **Laravel 13 · PHP 8.3 · MySQL 8 · Tabler (CDN) · Blade**. Tanpa build step frontend.

## 1. Instalasi

```powershell
cd "D:\project laravel\centralkitchen"
composer install
Copy-Item .env.example .env
php artisan key:generate
# buat database MySQL: mbg_central_kitchen (utf8mb4)
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Login default: `admin@mbg.id / password123` (super-admin), plus
`procurement@`, `gudang@`, `dapur@`, `driver@mbg.id` (password sama).

## 2. Environment (.env)

| Key | Default | Keterangan |
|---|---|---|
| `DB_*` | `mbg_central_kitchen` | Koneksi MySQL |
| `INVENTORY_LOT_METHOD` | `FEFO` | Metode alokasi batch |
| `INVENTORY_EXPIRY_ALERT_DAYS` | `30` | Ambang peringatan expired |
| `QC_AUTO_APPROVE` | `false` | QC selalu manual |
| `FINANCIAL_COSTING_METHOD` | `AVG` | Metode average costing |
| `CACHE_STORE` / `SESSION_DRIVER` / `QUEUE_CONNECTION` | `file`/`file`/`database` | Driver lokal |

Testing memakai SQLite `:memory:` + driver `array`/`sync` (lihat `phpunit.xml`).

## 3. Database, migrasi, seeding

- `php artisan migrate` — 13 migrasi: 9 domain (`2026_01_01_00000{1..9}`) + 4 vendor
  (spatie permission & activitylog).
- `php artisan migrate:fresh --seed` — reset + seed penuh.
- Seeder: `RolesPermissionsSeeder` (7 peran, 37 izin) → `MasterSeeder`
  (satuan, org, dapur, gudang, supplier, 8 sekolah, 15 bahan, 4 produk, resep, menu/Minggu, 5 user)
  → `DemoSeeder` (1 hari operasional penuh lewat ledger: demand→PR→6 PO→6 GR→produksi 480 porsi→QC→packaging→distribusi→3 delivery).
- Konvensi: PK `id`, FK + index + cascade/nullOnDelete eksplisit, `timestamps`,
  `softDeletes` untuk data bisnis, `decimal(15,3)` qty, `decimal(15,2)` uang,
  status sebagai `string` + index (kompatibel MySQL & SQLite).

## 4. Autentikasi, peran, izin

- Session guard (web) + Sanctum (API). Login dibatasi throttle, akun nonaktif ditolak.
- Peran: `super-admin`, `admin` (bypass semua ability via `Gate::before`),
  `procurement`, `warehouse`, `kitchen`, `driver`, `viewer`.
- Otorisasi 2 lapis: middleware `permission:{slug}` di setiap grup route +
  `AuthorizesOrgAccess::ensureOrgAccess()` di semua aksi detail (isolasi organisasi;
  `super-admin` dikecualikan). Terbukti oleh test `cross_organization_access_is_forbidden`.
- Audit: trait `Auditable` (spatie activitylog, fail-safe) + tabel `audit_logs`
  via `AuditService`, halaman `/audit-logs`.

## 5. Modul

Dashboard · Organisasi · Central Kitchen · Unit Dapur · Gudang · Supplier ·
Sekolah + Penerima (index + filter alergi) · Produk · Bahan · Satuan + Konversi ·
Menu + Gizi · Resep + Gizi · Demand · PR · PO · GR · Inventory · Mutasi ·
Batch/Expired · Opname · Transfer Gudang · Reservasi Stok · Rencana Produksi ·
Production Order · QC · Packaging · Distribusi · Delivery + Tracking + Bukti Foto ·
Waste · Costing · Laporan (stok/produksi/delivery/keuangan/expired) · Notifikasi ·
Audit Log · Users · Roles · Settings · API v1 (token, stok, delivery). Total ±165 route.

## 6. Alur bisnis inti

```
Demand → [generate PR: explosion resep × porsi] → PR (draft→submit→approve)
→ PO (draft→submit→approve) → GR (parsial allowed, posting stok+batch)
→ Inventory (FEFO) → Rencana Produksi → WO (plan→release→start→consume→complete)
→ QC (PASSED/FAILED/CONDITIONAL) → Packaging → Distribusi (delivery/sekolah otomatis)
→ Delivery (dispatch→deliver/partial/fail + tracking) → Sekolah
→ Laporan + Costing (material aktual dari ledger)
```

## 7. Inventory workflow & engine (`App\Services\InventoryService`)

- **Semua mutasi lewat service**, dalam `DB::transaction` + `lockForUpdate`
  pada `inventory_stocks`. Tidak ada update qty langsung dari controller.
- Tipe: `PURCHASE_RECEIPT, STOCK_IN, STOCK_OUT, PRODUCTION_CONSUMPTION,
  PRODUCTION_OUTPUT, TRANSFER, ADJUSTMENT, STOCK_OPNAME, WASTE, DELIVERY, RETURN`.
- Setiap mutasi menulis 1 baris **append-only** `inventory_movements`
  (`stock_before/after`, `unit_cost`, referensi). Konsistensi:
  `Σ(IN−OUT) = Σ(inventory_stocks)` — dicek test + script konsistensi.
- **FEFO**: batch `AVAILABLE`, qty>0, belum expired, urut expiry tercepat
  (NULL terakhir). Batch `BLOCKED`/`EXPIRED` tidak ikut alokasi.
- **Idempotency**: kombinasi (`movement_type`, `reference_type`, `reference_id`)
  unik — posting ganda ditolak (`RuntimeException`).
- Partial: GR parsial (status PO `PARTIAL→COMPLETED`), konsumsi parsial,
  delivery parsial + retur. Reservasi stok terpisah dari mutasi fisik.
- Opname: snapshot sistem → hitung fisik → approve → posting selisih
  sebagai movement `STOCK_OPNAME` per item.
- Costing: `CostingService::forProductionOrder` memakai `total_cost` aktual
  dari ledger konsumsi + biaya manual.

## 8. Production / delivery workflow

WO membutuhkan resep (kebutuhan bahan dihitung `qty × porsi/yield × (1+susut)`).
Konsumsi hanya untuk WO `RELEASED/IN_PROGRESS/PARTIAL`; penyelesaian menulis
output produk + costing otomatis. Delivery mengurangi stok produk via FEFO;
tracking bebas (`PLANNED→IN_TRANSIT→DELIVERED/PARTIAL/FAILED`).

## 9. Testing

```powershell
php artisan test                                   # semua (45 test, 219+ assertion)
php artisan test --filter=InventoryTest            # ledger: FEFO, stok kurang, duplikat, rollback, konsistensi
php artisan test --filter="ProcurementTest|ProductionTest|DistributionTest"
php artisan test --filter=AuthTest                 # login, RBAC, isolasi org, render semua halaman
php artisan test --filter=ApiTest                  # token Sanctum, stok, tracking kurir, isolasi org
php artisan test --filter=WorkflowExtrasTest       # units, transfer, reservasi, recipients, nutrisi, bukti foto, notifikasi
```

## 11. API v1 (Sanctum)

```
POST /api/v1/token                 # email+password → bearer token (throttle login)
GET  /api/v1/me                    # profil + peran (auth:sanctum)
POST /api/v1/logout                # cabut token aktif
GET  /api/v1/stock/summary|low|expiring|movements
GET  /api/v1/deliveries[?mine=1]   # kurir: delivery-nya; tracking tanpa mutasi stok
POST /api/v1/deliveries/{id}/track # status + GPS (validasi -90..90 / -180..180)
```

Semua endpoint terotentikasi, ter-skup organisasi/kitchen. Mutasi stok
(serah terima) tetap lewat UI web agar melewati ledger + FEFO yang sama.

## 12. Notifikasi workflow

Bell di navbar + halaman `/notifications`. Pemicu otomatis: PR disetujui/ditolak
→ peminta; PO disetujui → gudang/pengadaan; GR → peringatan stok masih di bawah
minimum; WO dirilis → tim dapur; QC FAILED/CONDITIONAL → admin; delivery selesai
→ admin; scheduler expiry/low-stock → terkait. Peran memakai nama seed
(`admin`, `warehouse`, `kitchen`, …) dan ter-skup organisasi.

## 13. Referensi CiptaCMS

Kualitas: `./vendor/bin/pint --test` (style), kompilasi 79 Blade terverifikasi,
`composer validate` OK.

## 10. Operasional & deployment

- Scheduler: `mbg:expiry-check` (harian 06:00, menandai `EXPIRED` + notifikasi)
  dan `mbg:low-stock-check` (per jam). `php artisan schedule:list` untuk verifikasi.
- Production: `APP_DEBUG=false`, `php artisan config:cache route:cache view:cache`,
  `migrate --force`, supervisor untuk `queue:work`, backup MySQL harian.
- Keamanan: validasi di semua form, CSRF, mass-assignment (`$fillable`),
  upload bukti delivery dibatasi path storage, session `http_only`/`same_site=lax`,
  password `bcrypt(12)`, throttle login.

## 14. Referensi CiptaCMS

Pola yang diadaptasi (read-only, tanpa merusak sumber): struktur
`bootstrap/app.php` Laravel 13, `SettingService`/menu composer, RBAC
`Gate::before` untuk admin, artisan console kernel modern. Domain kitchen
sepenuhnya baru dan mandiri.

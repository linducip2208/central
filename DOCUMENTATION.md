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
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

Login default: `admin@mbg.id / password123` (super-admin); plus
`procurement@`, `gudang@`, `dapur@`, `driver@`, `sekolah@mbg.id` (password sama).

## 2. Environment

`DB_*` (default `mbg_central_kitchen`), `INVENTORY_LOT_METHOD=FEFO`,
`INVENTORY_EXPIRY_ALERT_DAYS=30`, `QC_AUTO_APPROVE=false`,
`FINANCIAL_COSTING_METHOD=AVG`, `CACHE_STORE/SESSION_DRIVER=file`,
`QUEUE_CONNECTION=database`. Testing: SQLite `:memory:` + `array`/`sync` (phpunit.xml).

## 3. Database, migrasi, seeding

- `php artisan migrate` — 23 migrasi: 19 domain (`2026_01_01_00000{1..9}`,
  `000010` sanctum, `000011` master expansion, `000012` planning,
  `000013` quality/trace, `000014` alter operasional, `000015` indeks performa)
  + 4 vendor (spatie permission & activitylog).
- `php artisan migrate:fresh --seed` — reset + seed penuh.
- Seeder: `RolesPermissionsSeeder` (8 peran, 56 izin) → `MasterSeeder`
  (satuan, org, dapur, gudang, supplier, 8 sekolah, 15 bahan, 4 produk, resep,
  menu/minggu, 5 user, 20 penerima) → `DemoSeeder` (1 hari operasional penuh
  lewat ledger) → `ExpansionSeeder` (alergen, meal group, kendaraan, rute+stop,
  lokasi WMS, work center, template inspeksi, price list, bahan kemasan,
  siklus menu, settings pajak/currency, feature flags, user sekolah).
- Konvensi: PK `id`, FK + index + cascade/nullOnDelete, `timestamps`,
  `softDeletes` data bisnis, `decimal(15,3)` qty, `decimal(15,2)` uang,
  status `string` + index (kompatibel MySQL & SQLite).

## 4. Autentikasi, peran, izin

- Session guard (web) + Sanctum (API). Throttle login, akun nonaktif ditolak.
- Peran: `super-admin`, `admin` (bypass via `Gate::before`), `procurement`,
  `warehouse`, `kitchen`, `driver`, `school`, `viewer`.
- Otorisasi 3 lapis: middleware `permission:{slug}` + `ensureOrgAccess()` di
  semua aksi detail + `ensureKitchen/Warehouse/School()` di semua input ID
  referensi + 8 Policy terdaftar (`Gate::policy`). `super-admin` dikecualikan.
- Audit: trait `Auditable` (fail-safe), tabel `audit_logs`, listener login/logout,
  approval log, halaman `/audit-logs` + `/approvals`.

## 5. Modul (IMPLEMENTED)

Dashboard · Demand + Demand Plans · MRP · BOM · Menu + Siklus + Gizi ·
Resep (+waktu, effective-date, gizi) · PR · RFQ + Quotation + Award · PO ·
GR · Invoice + 3-way match · Inventory + Ledger · Batch/Expired · Lokasi WMS +
Scan Barcode · Traceability · Opname · Transfer · Reservasi · Rencana Produksi ·
WO/MES (work center, operator, downtime, material check, teoritis vs aktual) ·
QC klasik + QMS (inspeksi, NCR/CAPA, suhu CCP, karantina) · Recall ·
Packaging (+material) · Distribusi · Delivery (+POD foto, GPS, geofence) · Retur (restock/waste) · Rute TMS +
Control Tower · Portal Sekolah · Waste + Analytics · Costing (standar vs aktual,
riwayat harga) · Laporan (13 jenis: +AP aging, biaya sekolah) · Analytics eksekutif + CSV · Notifikasi ·
Webhooks · 2FA + Token API + Profil · Budget vs Realisasi · Tutup Periode · Laporan terjadwal · Users/Roles/Settings/Feature Flags · API v1 (24 endpoint). ±270 route.

## 6. Alur bisnis inti

```
DEMAND → MENU → RECIPE/BOM → DEMAND PLAN → MRP → PR → RFQ → QUOTATION → PO
→ GR (batch) → INVENTORY (FEFO) → PLAN → WO (check→issue→complete) → QC/QMS
→ PACKAGING → DISTRIBUSI → DELIVERY → SEKOLAH (portal) → COSTING → ANALITIK → AUDIT
```

## 7. Inventory engine (`App\Services\InventoryService`)

Semua mutasi lewat service, `DB::transaction` + `lockForUpdate`, ledger
append-only (`stock_before/after`, biaya, referensi). Tipe: 11 movement.
Konsistensi `Σ(IN−OUT) = Σ(stok)` (test + cek manual). FEFO lewati
expired/blocked. Idempotency per (tipe, ref). Parsial di GR/produksi/delivery.
Opname snapshot→hitung→approve→posting. Transfer = consume FEFO + receive batch
baru. Reservasi mengurangi availability tanpa mutasi fisik.

## 8. BOM (`App\Services\BomService`)

Multi-level (produk sebagai sub-assembly), explosion rekursif (max depth 10),
deteksi siklus saat explode DAN saat tambah komponen, scaling qty, yield,
scrap/waste %, konversi ke satuan dasar, seleksi versi effective-date.
Approval mengarsipkan versi lama (histori reproduksibel).

## 9. Demand planning + MRP

Demand plan: gross per sekolah×hari − ketidakhadiran + manual + safety
(dihitung otomatis di model). MRP: explosion BOM/resep → gross; kurangi
on-hand, reservasi, incoming PO; tambah safety (max setting vs forecast);
net → saran beli (MOQ, supplier preferensi/termurah) dengan penjelasan
per baris. Konversi garis → draft PR.

## 10. Procurement 2.0

RFQ multi-supplier → quotation per item → award otomatis termurah yang memenuhi
qty → PO. Invoice: unik per (supplier, no), 3-way match (qty vs GR, harga vs PO),
variansi ditampilkan, verifikasi ditolak bila variansi, status bayar,
`invoice_status` di PO. Kontak/alamat/kontrak/price-list per supplier.

## 11. WMS

Gudang → Zona → Rak → Bin (barcode otomatis). Put-away batch ke bin.
Karantina (BLOCKED + alasan) keluar dari FEFO; release mengembalikan.
Halaman scan: input keyboard-wedge + kamera via BarcodeDetector (progressive
enhancement). Lookup bin & batch → link genealogy.

## 12. Production / MES

Siklus: PLANNED → RELEASED (+notifikasi dapur) → MATERIAL_CHECK → IN_PROGRESS
→ QC → COMPLETED/PARTIAL → PACKAGED → DISPATCHED. Material check validasi
ketersediaan + hitung biaya teoritis. Operator/work-center assignment.
Downtime tercatat + durasi. Tabel kebutuhan: teoritis vs aktual vs variansi.
Capacity planning harian (beban vs kapasitas 8 jam, flag OVERLOAD/TIGHT/OK).

## 13. QMS / Food Safety

Template inspeksi (parameter + spec min/max). Inspeksi INCOMING/IN_PROCESS/
FINISHED dengan foto; gagal otomatis → notifikasi + webhook + NCR.
NCR: disposisi HOLD/REJECT mengkarantina batch; CAPA corrective/preventive;
NCR close otomatis bila CAPA selesai. Temperature log CCP dengan spec bawaan
per checkpoint; OOR wajib tindakan koreksi + peringatan.

## 14. Traceability & Recall

Genealogy forward (supplier→GR→produksi→batch jadi→delivery→sekolah) dan
backward, dari ledger + referensi dokumen. Recall: hitung batch terdampak
otomatis → aktivasi mengkarantina + notifikasi + webhook → contain → close.
Laporan recall + halaman trace per batch.

## 15. Nutrisi & alergen

Gizi per resep & menu vs target; matriks alergen bahan↔penerima (pivot severity);
meal group diet; filter penerima alergi; laporan nutrisi (tercukupi/di bawah target).

## 16. Packaging & TMS & Portal

Pemakaian material kemasan (kategori PACKAGING) keluar stok via ledger.
TMS: rute + stop berurutan + window; apply rute mengisi kendaraan/kurir/urutan/
ETA delivery; control tower (terkirim/terlambat/POD/exception). Portal sekolah
(peran `school`): konfirmasi terima/tolak/kehadiran/keluhan/masukan + daftar keluhan.

## 17. Costing & waste & analytics

Costing aktual dari ledger + standar dari resep (variansi material & per porsi),
riwayat harga beli per bahan. Waste analytics per alasan + tren. Executive
dashboard (belanja, porsi, service level, waste, biaya/porsi, QC fail rate)
+ tren + advisory deterministik + export CSV (5 dataset). Laporan: stok,
produksi, delivery, finansial, expired, intelligence (stockout/excess/turnover/
dead stock), waste, supplier scorecard, recall, nutrisi.

## 18. API v1 (Sanctum, 22 endpoint)

```
POST /api/v1/token · GET /api/v1/me · POST /api/v1/logout
GET  /api/v1/stock/summary|low|expiring|movements
GET  /api/v1/deliveries[?mine=1] · GET /api/v1/deliveries/{id}
POST /api/v1/deliveries/{id}/track        # GPS -90..90/-180..180
GET  /api/v1/schools[/{id}] · GET /api/v1/recipients[?school_id][?allergy] · GET /api/v1/allergens
GET  /api/v1/demand-plans · GET /api/v1/mrp-runs[/{id}] · GET /api/v1/boms · POST /api/v1/bom-explode
GET  /api/v1/production-orders[/{id}] · GET /api/v1/inspections · GET /api/v1/recalls[/{id}] · GET /api/v1/invoices
```

Throttle `api`, skop organisasi/kitchen, mutasi stok tetap via web (ledger sama).
Arsitektur siap v2 tanpa merusak v1 (prefix versioning).

## 19. Webhooks & approvals & notifikasi

10 event (`stock.updated` … `recall.created`), langganan per org, secret
HMAC-SHA256, queue + retry 5× + backoff, log pengiriman, retry manual,
rotate secret. Approval engine reusable (append-only; dipakai PR/PO/BOM/
siklus/NCR/invoice/recall + inbox). Event Laravel → webhook + notifikasi
in-app (bell navbar + unread badge). Scheduler expiry/low-stock.

## 20. AI-ready (tanpa LLM)

`AiAdvisorInterface` + `DeterministicAdvisor` (moving average, safety stock,
days-of-stock, stockout risk, expiry/waste advisory) — explainable, tidak
memutasi data. Adapter LLM masa depan hanya boleh mengembalikan AdvisoryResult.

## 21. Multi-tenant & PWA

Isolasi org di semua query tenant + test lintas-org. Feature flags (cache),
settings per install, tenant export via seeder per org (roadmap: CLI).
PWA: manifest + service worker (cache CDN statis saja, TIDAK cache HTML
autentikasi) + layar operasional mobile-friendly + scan barcode.

## 22. Testing

`php artisan test` — 118 test: auth/RBAC/isolasi-org, render ±70 halaman,
inventory (FEFO/rollback/duplikat/konsistensi), procurement (+RFQ/invoice),
produksi (+MES/capacity), distribusi, BOM (explosion/siklus/versi),
MRP (netting/explanation/to-PR), WMS (lokasi/putaway/karantina/scan),
QMS (fail→NCR→karantina→CAPA, suhu OOR), traceability (maju/mundur/recall),
TMS/portal, platform (approval/webhook/audit-login/analytics/API),
edge cases (stok kurang/expired/parsial/duplikat/GPS invalid/QC gagal/
recall/BOM cycle/konversi invalid/hapus referensi).

## 23. Operasional & keamanan

Scheduler, `APP_DEBUG=false`, cache config/route/view, `migrate --force`,
`queue:work` via supervisor, backup MySQL. Keamanan: validasi semua input
(termasuk GPS range, MIME upload, ID referensi se-organisasi), CSRF,
`$fillable`, upload terbatas, throttle, bcrypt(12), tanpa secret di repo,
rate-limit API, token Sanctum per-device + pencabutan.

## 24. Status: IMPLEMENTED vs ROADMAP

IMPLEMENTED: semua modul §5 + engine §7–§21 + 118 test.
ROADMAP (butuh sistem eksternal): payment gateway riil, SMS gateway,
aplikasi mobile native, API v2 (flag sudah ada, nonaktif), SSO/LDAP,
multi-database per tenant, EDI supplier.

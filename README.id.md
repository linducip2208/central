# MBG Central Kitchen

<<<<<<< HEAD
Platform ERP untuk mengelola operasional Central Kitchen program Makan Bergizi Gratis (MBG) secara terintegrasi dan multi-organisasi.

**Stack:** Laravel 13 · PHP 8.3 · MySQL 8 · Blade · Tabler · Sanctum · Spatie Permission · Activity Log

## Rilis Saat Ini

**v1.0 — MBG Central Kitchen ERP**

Versi utama saat ini telah mencakup core operasional:

- Isolasi data antar organisasi
- Organisasi, Central Kitchen, Unit Dapur dan Gudang
- Supplier, Sekolah dan Penerima
- Filter alergi penerima untuk kebutuhan packing
- Bahan, Produk, Satuan dan Konversi Satuan
- Menu, Resep dan Nutrisi
- Demand planning dan perhitungan kebutuhan berdasarkan resep
- Purchase Request (PR), Purchase Order (PO) dan Goods Receipt (GR)
- Penerimaan parsial
- Inventory ledger dengan transaksi dan locking database
- Batch, expiry dan FEFO
- Mutasi stok, stock opname, transfer gudang dan reservasi/release stok
- Production Planning dan Production Order
- Perhitungan kebutuhan bahan dan konsumsi produksi
- Output produksi dan costing otomatis
- Quality Control dengan hasil PASS/FAIL/CONDITIONAL
- Packaging dan distribusi
- Delivery, tracking GPS kurir dan penanganan parsial/gagal
- Bukti foto delivery
- Waste
- Average costing dan laporan operasional
- Notifikasi workflow dengan unread badge
- Audit log
- RBAC dan authorization berbasis organisasi
- API v1 berbasis Sanctum untuk auth, stok dan tracking delivery
- UI responsive berbasis Tabler
- Automated test dan regression test

## Alur Bisnis Inti

```
Demand
 → Explosion Resep
 → Purchase Request
 → Purchase Order
 → Goods Receipt
 → Inventory + Batch + FEFO
 → Production Planning
 → Production Order
 → Konsumsi Bahan
 → Quality Control
 → Packaging
 → Distribusi
 → Delivery
 → Sekolah / Penerima
 → Laporan + Costing + Audit
```

## Engine Inventory

Semua mutasi fisik stok dipusatkan melalui inventory service:

- Database transaction
- `lockForUpdate()`
- Ledger movement append-only
- FEFO
- Batch dan expiry control
- Konversi satuan
- Operasi parsial
- Reservasi stok
- Idempotency
- Konsistensi stok
- Costing produksi berdasarkan konsumsi ledger

## API v1

Saat ini tersedia API terlindungi Sanctum untuk:

- Login/logout token
- Profil user
- Ringkasan stok
- Stok minimum
- Stok mendekati expiry
- Inventory movements
- Daftar delivery kurir
- Tracking GPS delivery

Mutasi stok tetap melalui workflow web agar ledger dan aturan FEFO selalu digunakan.

## Roadmap Pengembangan Luas

### 1. Planning & Forecasting

- Analitik demand historis
- Forecast harian/mingguan/bulanan
- Prediksi demand per sekolah
- Kalender libur dan faktor musiman
- Capacity planning
- Forecast kebutuhan bahan
- Rekomendasi replenishment otomatis
- What-if planning dan simulasi skenario

### 2. Procurement Lanjutan

- Supplier quotation
- RFQ / tender
- Supplier portal
- Riwayat harga supplier
- Kontrak dan price list
- Supplier performance scorecard
- Saran PO otomatis
- Kontrol budget pembelian
- Three-way matching PO → GR → invoice
- Invoice dan pembayaran
- Approval matrix procurement

### 3. Warehouse / WMS

- Barcode dan QR scanning
- Receiving via mobile
- Put-away dan picking
- Bin/rack/location
- Cycle counting
- FEFO picking wave
- Allocation reservation
- Stock aging
- Slow-moving inventory
- Quarantine / quality hold
- Lot traceability dan recall
- Replenishment antar gudang

### 4. Production Management

- Kalender produksi
- Capacity planning dapur
- Work center
- Penugasan operator
- Batch production scheduling
- Versioning resep
- Yield variance
- Actual vs theoretical consumption
- Downtime produksi
- Maintenance peralatan
- Dashboard performa produksi

### 5. Food Safety & Quality

- Kontrol berbasis HACCP
- Critical Control Point
- Temperature log
- Checklist cleaning dan sanitation
- Incoming QC
- In-process QC
- Finished-goods QC
- Non-conformance
- CAPA
- Quarantine
- Recall
- Batch genealogy lengkap

### 6. Nutrition & Menu Intelligence

- Database nutrisi
- Nutrisi per porsi
- Allergen matrix
- Dietary restriction
- Menu cycle planning
- Approval menu
- Laporan compliance nutrisi
- Analisis cost vs nutrition
- Saran substitusi menu
- Bahan alternatif yang disetujui

### 7. Distribution & Logistics

- Route planning
- Optimasi multi-stop
- Driver assignment
- Vehicle management
- Delivery time window
- Proof of delivery
- Konfirmasi penerimaan digital
- Geofencing
- Live fleet map
- Delivery exception
- Return-to-kitchen
- Monitoring temperatur produk sensitif

### 8. School & Recipient Portal

- Portal sekolah
- Konfirmasi order harian
- Roster penerima
- Pencatatan kehadiran/pengambilan makanan
- Allergy alert
- Rekonsiliasi distribusi
- Feedback sekolah
- Complaint dan incident management
- Dashboard per sekolah

### 9. Finance & Cost Control

- Purchase invoice
- Accounts payable
- Budget vs actual
- Cost center
- Alokasi biaya dapur/sekolah
- Production cost variance
- Waste cost analysis
- Supplier spend analysis
- Period closing
- Integrasi software accounting

### 10. Analytics & BI

- Executive dashboard
- KPI library
- KPI procurement
- KPI inventory
- KPI production
- KPI QC
- KPI delivery
- KPI waste
- KPI biaya
- KPI service level sekolah
- Drill-down
- Scheduled reports
- Export CSV/Excel/PDF

### 11. Automation & AI

- Rekomendasi reorder
- Risiko expiry
- Forecast demand
- Rekomendasi procurement
- Perbandingan supplier
- Bantuan production planning
- Anomaly detection
- Analisis pola waste
- Prediksi keterlambatan delivery
- Laporan operasional dengan bahasa natural
- AI assistant dengan akses berbasis permission

AI harus bersifat membantu keputusan dan tetap tunduk pada isolasi organisasi, role dan permission.

### 12. Mobile / PWA

- Workflow gudang mobile
- Workflow produksi mobile
- QC mobile
- Driver app/PWA
- Barcode/QR scanner
- Offline-first delivery
- GPS background jika diaktifkan
- Push notification

### 13. Integration Platform

- API v2
- Webhook
- Integrasi sistem sekolah
- Integrasi pelaporan pemerintah jika diperlukan
- Accounting
- Payment/banking
- Maps/routing
- Barcode/label printer
- IoT sensor temperatur
- Import/export connector

### 14. Enterprise & SaaS

- Multi-tenancy lebih kuat
- Subscription plan
- Feature flag
- Konfigurasi per tenant
- Platform administration
- Usage metering
- Compliance center
- Backup/restore
- SSO/OAuth
- 2FA
- API key management
- Webhook management
- Tenant data export
- Data retention

### 15. Governance & Compliance

- Document management
- SOP management
- Approval workflow
- Digital signature
- Policy acknowledgement
- Segregation of duties
- Advanced audit trail
- Data retention
- Incident management
- Business continuity
- Disaster recovery

## Prinsip Pengembangan

- Semua mutasi inventory melalui ledger service.
- Semua data bisnis harus terisolasi berdasarkan organisasi.
- Authorization menggunakan policy dan permission.
- Operasi perubahan status penting harus memakai transaction.
- Posting ganda harus dicegah dengan idempotency.
- Stok perishables menggunakan FEFO.
- Business calculation dibuat testable di luar controller.
- Hindari N+1 dan query laporan yang tidak terkontrol.
- UI Tabler harus konsisten.
- Setiap workflow kritis wajib memiliki automated test.
- Jangan menandai fitur selesai jika masih ada TODO, placeholder atau alur rusak.

## Quality Gate

```text
composer validate
Pint
PHPUnit / php artisan test
Blade compilation
Route validation
Migration + seed validation
Authorization / organization-isolation tests
Inventory ledger consistency
Critical workflow regression
Security audit
Performance / N+1 audit
TODO / FIXME / placeholder audit
Documentation update
```

## Dokumentasi

- English: [README.md](README.md)
- العربية: [README.ar.md](README.ar.md)
- Dokumentasi teknis: [DOCUMENTATION.md](DOCUMENTATION.md)

## Lisensi

Proyek proprietary. Hak penggunaan mengikuti perjanjian repository/deployment.
=======
ERP Central Kitchen untuk program *Makan Bergizi Gratis* (MBG).
**Laravel 13 · PHP 8.3 · MySQL 8 · Blade + Tabler UI · API Sanctum**

## Mulai cepat

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
# buat database MySQL: mbg_central_kitchen (utf8mb4)
php artisan migrate --seed
php artisan serve
```

Login default: `admin@mbg.id / password123` (plus `procurement@`, `gudang@`, `dapur@`, `driver@`, `sekolah@mbg.id`).

## Yang sudah diimplementasikan

**Alur inti:** Demand → Menu → Resep/BOM → MRP → Procurement (PR → RFQ → Quotation → PO → GR → Invoice 3-way match) → Ledger inventory (FEFO, batch, expired) → Produksi/MES (work center, operator, downtime, teoritis vs aktual) → QC/QMS (inspeksi, NCR/CAPA, suhu CCP, karantina) → Packaging (+material) → Distribusi/TMS (rute, control tower) → Delivery (foto POD, tracking GPS) → Portal sekolah (konfirmasi, keluhan) → Costing (standar vs aktual) → Analitik/BI + export CSV → Audit.

**Engine:** Explosion BOM (multi-level, deteksi siklus, versioning) · MRP (gross → on-hand → reservasi → incoming → safety → net, explainable) · forecast deterministik + seam AI-ready (tanpa ketergantungan LLM) · genealogy batch maju/mundur + recall · approval engine reusable · webhook bertanda tangan + retry · API v1 Sanctum.

**Jaminan:** ledger append-only, `DB::transaction` + `lockForUpdate`, FEFO (expired/diblokir tidak terkonsumsi), reservasi mengurangi ketersediaan, posting idempoten, isolasi organisasi di semua query tenant, policies + middleware permission, jejak audit lengkap.

## Dok & uji

- Dokumentasi lengkap: [DOCUMENTATION.md](DOCUMENTATION.md).
- Uji: `php artisan test` (80+ test), `./vendor/bin/pint --test`, `composer validate`.
- Scheduler: `mbg:expiry-check` (harian), `mbg:low-stock-check` (per jam).
- Siap PWA (`/manifest.webmanifest`, `/sw.js`), halaman operasional mobile-friendly, halaman scan barcode.

## Status

Lihat [DOCUMENTATION.md](DOCUMENTATION.md) untuk pembagian IMPLEMENTED / ROADMAP.
Yang butuh sistem eksternal: payment gateway riil, SMS gateway, aplikasi mobile native.
>>>>>>> 4917424 (Central Kitchen Platform: BOM MRP WMS QMS MES TMS BI and full operational expansion)

---

## Roadmap Pengembangan Luas

### 1. Planning & Forecasting

- Analitik demand historis
- Forecast harian/mingguan/bulanan
- Prediksi demand per sekolah
- Kalender libur dan faktor musiman
- Capacity planning
- Forecast kebutuhan bahan
- Rekomendasi replenishment otomatis
- What-if planning dan simulasi skenario

### 2. Procurement Lanjutan

- Supplier quotation
- RFQ / tender
- Supplier portal
- Riwayat harga supplier
- Kontrak dan price list
- Supplier performance scorecard
- Saran PO otomatis
- Kontrol budget pembelian
- Three-way matching PO G�� GR G�� invoice
- Invoice dan pembayaran
- Approval matrix procurement

### 3. Warehouse / WMS

- Barcode dan QR scanning
- Receiving via mobile
- Put-away dan picking
- Bin/rack/location
- Cycle counting
- FEFO picking wave
- Allocation reservation
- Stock aging
- Slow-moving inventory
- Quarantine / quality hold
- Lot traceability dan recall
- Replenishment antar gudang

### 4. Production Management

- Kalender produksi
- Capacity planning dapur
- Work center
- Penugasan operator
- Batch production scheduling
- Versioning resep
- Yield variance
- Actual vs theoretical consumption
- Downtime produksi
- Maintenance peralatan
- Dashboard performa produksi

### 5. Food Safety & Quality

- Kontrol berbasis HACCP
- Critical Control Point
- Temperature log
- Checklist cleaning dan sanitation
- Incoming QC
- In-process QC
- Finished-goods QC
- Non-conformance
- CAPA
- Quarantine
- Recall
- Batch genealogy lengkap

### 6. Nutrition & Menu Intelligence

- Database nutrisi
- Nutrisi per porsi
- Allergen matrix
- Dietary restriction
- Menu cycle planning
- Approval menu
- Laporan compliance nutrisi
- Analisis cost vs nutrition
- Saran substitusi menu
- Bahan alternatif yang disetujui

### 7. Distribution & Logistics

- Route planning
- Optimasi multi-stop
- Driver assignment
- Vehicle management
- Delivery time window
- Proof of delivery
- Konfirmasi penerimaan digital
- Geofencing
- Live fleet map
- Delivery exception
- Return-to-kitchen
- Monitoring temperatur produk sensitif

### 8. School & Recipient Portal

- Portal sekolah
- Konfirmasi order harian
- Roster penerima
- Pencatatan kehadiran/pengambilan makanan
- Allergy alert
- Rekonsiliasi distribusi
- Feedback sekolah
- Complaint dan incident management
- Dashboard per sekolah

### 9. Finance & Cost Control

- Purchase invoice
- Accounts payable
- Budget vs actual
- Cost center
- Alokasi biaya dapur/sekolah
- Production cost variance
- Waste cost analysis
- Supplier spend analysis
- Period closing
- Integrasi software accounting

### 10. Analytics & BI

- Executive dashboard
- KPI library
- KPI procurement
- KPI inventory
- KPI production
- KPI QC
- KPI delivery
- KPI waste
- KPI biaya
- KPI service level sekolah
- Drill-down
- Scheduled reports
- Export CSV/Excel/PDF

### 11. Automation & AI

- Rekomendasi reorder
- Risiko expiry
- Forecast demand
- Rekomendasi procurement
- Perbandingan supplier
- Bantuan production planning
- Anomaly detection
- Analisis pola waste
- Prediksi keterlambatan delivery
- Laporan operasional dengan bahasa natural
- AI assistant dengan akses berbasis permission

AI harus bersifat membantu keputusan dan tetap tunduk pada isolasi organisasi, role dan permission.

### 12. Mobile / PWA

- Workflow gudang mobile
- Workflow produksi mobile
- QC mobile
- Driver app/PWA
- Barcode/QR scanner
- Offline-first delivery
- GPS background jika diaktifkan
- Push notification

### 13. Integration Platform

- API v2
- Webhook
- Integrasi sistem sekolah
- Integrasi pelaporan pemerintah jika diperlukan
- Accounting
- Payment/banking
- Maps/routing
- Barcode/label printer
- IoT sensor temperatur
- Import/export connector

### 14. Enterprise & SaaS

- Multi-tenancy lebih kuat
- Subscription plan
- Feature flag
- Konfigurasi per tenant
- Platform administration
- Usage metering
- Compliance center
- Backup/restore
- SSO/OAuth
- 2FA
- API key management
- Webhook management
- Tenant data export
- Data retention

### 15. Governance & Compliance

- Document management
- SOP management
- Approval workflow
- Digital signature
- Policy acknowledgement
- Segregation of duties
- Advanced audit trail
- Data retention
- Incident management
- Business continuity
- Disaster recovery

## Lisensi

Proyek proprietary. Hak penggunaan mengikuti perjanjian repository/deployment.

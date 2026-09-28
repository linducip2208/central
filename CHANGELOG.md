# Changelog

Semua perubahan penting dicatat di sini. Format tanggal: YYYY-MM-DD.

## [Unreleased]

## [1.2.0] — 2026-09-28 — Enterprise command batch

### Ditambahkan
- KPI terpusat (`KpiService`) + perbandingan periode + control tower lintas modul
  dengan drill-down; laporan harian dapur + laporan eksepsi.
- Automation engine (WHEN/IF/THEN) + template/preferensi notifikasi + mail
  graceful-fallback + interface WhatsApp (adapter log) + command eskalasi.
- Documents/SOP (lifecycle + acknowledgement), global search (skop org+izin),
  breadcrumbs, import engine generik (preview/dry-run/commit/riwayat).
- Supplier returns (stok OUT + klaim, guard sisa terima).
- BOM: reverse (used-in), clone revisi, banding + simulasi ketersediaan.
- Demand forecast berversi + MAPE aktual + skenario what-if.
- MRP: order multiple, surplus/max-stock, expiry-aware availability, saran
  transfer antar gudang, flag SHORTAGE/SURPLUS.
- WMS: metode FIFO per gudang, picking (reservasi per delivery),
  rekonsiliasi (ledger/agregat/batch/reservasi/valuasi), barcode item.
- MES: shift, peralatan + maintenance log, rework, planned vs actual time.
- Approval: matriks nominal + delegasi + enforcement + UI.
- Keuangan: AP aging, budget vs realisasi, alokasi biaya sekolah, period
  closing di semua titik posting.
- Keamanan: 2FA TOTP (vektor RFC lolos uji), token API per pengguna, GPS
  geofence + validasi range, guard tenant di semua ID referensi.
- Operasional: health dashboard, backup (mysqldump+fallback), cleanup,
  scheduled reports, tenant export, subscription plans, PWA, hygiene checklist.
- API: resources, envelope error + trace_id, skeleton v2, 24 endpoint, API.md.
- Dok: README(.id/.ar) + roadmap gabungan, DOCUMENTATION 24 seksi,
  DEPLOYMENT, ADMIN/OPERATOR guides, CHANGELOG ini.

### Diperbaiki
- Kebocoran `{{var}}` di Blade (konstanta tak terdefinisi) — audit menyeluruh.
- Perbandingan tanggal kolom `date` di SQLite memakai `whereDate`.
- Koordinat 0,0 valid untuk geofence/optimasi.
- 3-way match memperhitungkan variansi pajak.

## [1.1.0] — 2026-09-28 — Enterprise expansion

### Ditambahkan
- BOM: reverse (used-in), clone revisi, banding + simulasi ketersediaan.
- Demand forecast berversi + MAPE aktual + skenario what-if.
- MRP: order multiple, max-stock/surplus, expiry-aware availability, saran
  transfer antar gudang, flag SHORTAGE/SURPLUS, FIFO per gudang.
- WMS: picking (reservasi per delivery), rekonsiliasi (ledger vs agregat vs
  batch vs reservasi + valuasi), barcode item, scan lookup item.
- MES: shift, peralatan + maintenance log, rework, planned vs actual time.
- Approval: matriks nominal + delegasi + enforcement di PR/PO/invoice.
- Supplier returns (retur ke supplier mengurangi stok + klaim).
- Retur sekolah: restock (movement RETURN) / waste.
- Geofence delivery + optimasi urutan rute nearest-neighbor.
- Keuangan: AP aging, budget vs realisasi, alokasi biaya sekolah, period closing.
- Automation rules (WHEN/IF/THEN) + template/preferensi notifikasi + mail
  graceful-fallback + interface WhatsApp (adapter log).
- Documents/SOP (lifecycle + acknowledgement), global search, breadcrumbs.
- Import engine generik (preview/dry-run/commit/riwayat) + CSV penerima/bahan/sekolah.
- AI: AiProviderInterface, AiAdvisorService, AiContextBuilder, AiPermissionGuard,
  AiAuditLogger (default deterministik).
- API: resources, envelope error + trace_id, skeleton v2, 24 endpoint, API.md.
- Keamanan: 2FA TOTP, token API per pengguna, policies, guard tenant di semua ID.
- Operasional: health dashboard, backup, cleanup, eskalasi scheduler, tenant export,
  scheduled reports, subscription plans, PWA manifest/SW, hygiene checklist.

### Diperbaiki
- Perbandingan tanggal kolom `date` di SQLite memakai `whereDate`
  (delegasi, price list, BOM, resep).
- Validasi regex `period` budget memakai array syntax.
- Koordinat 0,0 diperlakukan valid (geofence/optimasi).
- 3-way match memperhitungkan variansi pajak (bila pajak ditagih).

## [1.0.0] — 2026-09-28 — Full ERP

- Ledger inventory FEFO, procurement PR→PO→GR, produksi + QC + packaging,
  distribusi + delivery + tracking, costing, laporan, Tabler UI, API v1,
  45 test hijau. Lihat commit `e97b81a`.

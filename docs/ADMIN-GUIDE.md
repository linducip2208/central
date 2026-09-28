# Panduan Administrator

## Peran & akses

| Peran | Fungsi |
|---|---|
| super-admin/admin | akses penuh + bypass policy |
| procurement | PR, PO, RFQ, invoice, MRP baca |
| warehouse | GR, stok, opname, WMS |
| kitchen | produksi, QC/QMS, menu |
| driver | delivery + tracking |
| school | portal konfirmasi |
| finance | verifikasi invoice, costing |
| qc_manager | QMS + approval QC |
| planning | demand, MRP, BOM, menu |
| auditor | audit, laporan (read-only) |
| viewer | baca |

Kelola di Users/Roles. Izin granular 60+ (`document.*`, `automation.*`,
`import.view`, …). Matriks nominal di Approvals → Matriks; delegasi
di Approvals → Delegasi.

## Operasional harian

1. **Control Tower** (`/tms/tower`): produksi, WO menunggu, risiko stok/expired,
   PO menunggu, NCR/CAPA, keterlambatan, keluhan.
2. **Laporan Harian Dapur** (`/reports/daily`): ringkasan + cetak.
3. **Kesehatan** (`/health`): DB, cache, queue, storage, jobs, webhook.
4. **Audit Log** + **Approvals inbox**: jejak semua keputusan.

## Period closing & backup

- Tutup periode di Sistem → Tutup Periode (mengunci posting mundur).
- Backup otomatis harian; export tenant via `php artisan mbg:tenant-export {id|kode}`.
- Restore: lihat DEPLOYMENT.md.

## Troubleshooting

| Gejala | Tindakan |
|---|---|
| Stok tidak cocok | Buka Inventory → Rekonsiliasi; selisih → opname |
| Posting ditolak "periode ditutup" | Cek Sistem → Tutup Periode |
| Webhook PENDING menumpuk | Jalankan `queue:work`, cek URL/secret penerima |
| Approval 403 padahal berhak | Cek matriks nominal + delegasi aktif |
| Login 2FA gagal | Pastikan jam server sinkron (TOTP ±30 dtk) |

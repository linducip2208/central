# Panduan Operator (Gudang · Dapur · QC · Kurir · Sekolah)

## Gudang (WMS)

1. Terima barang: Goods Receipt → pilih PO → isi qty/batch/expired → posting.
2. Put-away: Batches → catat bin (atau Scan barcode).
3. Transfer/reservasi/adjustment: menu Inventory (alasan wajib untuk adjustment).
4. Opname: buat → hitung fisik → approve → posting selisih.
5. Scan: tempel pemindai di halaman Scan, Enter otomatis mencari.
6. Retur supplier: buka GR → Retur ke supplier (tidak boleh melebihi sisa terima).

## Dapur (Produksi)

1. Rencana → Generate WO → Rilis → Material check → Konsumsi (FEFO) → Selesai.
2. Catat operator, shift, downtime bila ada gangguan.
3. Hasil reject/rework dicatat terpisah; costing dihitung otomatis.

## QC

1. Inspeksi (pilih template) → ukur → gagal otomatis bila di luar spec.
2. NCR → disposisi HOLD mengkarantina batch → CAPA → close.
3. Suhu CCP + checklist higiene diisi rutin per shift.

## Kurir (API / mobile)

1. Ambil token: `POST /api/v1/token` (email+password+device).
2. Lihat delivery: `GET /api/v1/deliveries?mine=1`.
3. Update posisi: `POST .../track` (status + lat/lon; status ARRIVED/DELIVERED
   ditolak bila di luar geofence sekolah).
4. Serah terima + foto POD tetap via web oleh admin/kurir.

## Sekolah (Portal)

1. Buka Portal → delivery terbaru → isi diterima/ditolak/kehadiran.
2. Keluhan/insiden dicatat di formulir yang sama; pantau di Keluhan.

# MBG Central Kitchen — API

Base URL: `{APP_URL}/api`. Versi stabil: **v1**. v2 berstatus preview
(`GET /api/v2/status`) dan tidak untuk produksi.

## Autentikasi

Sanctum bearer token (per-device, dapat dicabut dari Profil → Token API).

```http
POST /api/v1/token
Content-Type: application/json

{"email": "driver@mbg.id", "password": "password123", "device": "HP-Kurir"}
→ 200 {"token": "1|...", "user": {"id": 5, "name": "Kurir", "roles": ["driver"]}}
```

Gunakan header `Authorization: Bearer 1|...` untuk endpoint berikut.
`POST /api/v1/logout` mencabut token aktif. Throttle: 120/menit (`api`),
login 10/menit, 2FA 5/menit.

## Error envelope

```json
{"success": false, "message": "...", "errors": {}, "trace_id": "abc123"}
```

Status: 401 unauthenticated · 403 forbidden/tenant asing · 404 tidak ada ·
422 validasi · 429 throttle · 500 server (disamarkan saat `APP_DEBUG=false`).

## Endpoint

| Method & Path | Deskripsi | Catatan |
|---|---|---|
| `GET /health` | Health check | publik |
| `GET /api/v1/me` | Profil + peran | auth |
| `GET /api/v1/stock/summary?warehouse_id=` | Ringkasan stok + available | auth, skop dapur |
| `GET /api/v1/stock/low` | Di bawah minimum | auth |
| `GET /api/v1/stock/expiring?days=` | Batch hampir expired | auth |
| `GET /api/v1/stock/movements?type=` | Ledger (paginasi) | auth |
| `GET /api/v1/deliveries[?mine=1][&status=]` | Delivery (kurir: miliknya) | auth, resource |
| `GET /api/v1/deliveries/{id}` | Detail + item + tracking | auth, isolasi org |
| `POST /api/v1/deliveries/{id}/track` | Status + GPS | auth, geofence untuk ARRIVED/DELIVERED |
| `GET /api/v1/schools[?q=]` | Sekolah | auth |
| `GET /api/v1/schools/{id}` | Sekolah + 50 penerima | auth |
| `GET /api/v1/recipients[?school_id][?allergy]` | Penerima + alergen | auth |
| `GET /api/v1/allergens` | Katalog alergen | auth |
| `GET /api/v1/demand-plans` | Demand plans | auth |
| `GET /api/v1/mrp-runs[/{id}]` | Hasil MRP + garis | auth |
| `GET /api/v1/boms` | BOM aktif | auth |
| `POST /api/v1/bom-explode` | `{product_id, qty}` → kebutuhan | auth, 422 bila siklus |
| `GET /api/v1/production-orders[/{id}]` | WO | auth |
| `GET /api/v1/inspections` | Inspeksi QMS | auth |
| `GET /api/v1/recalls[/{id}]` | Recall + batch | auth |
| `GET /api/v1/invoices` | Invoice supplier | auth |

Contoh tracking kurir:

```http
POST /api/v1/deliveries/12/track
{"status": "ARRIVED", "latitude": -6.1755, "longitude": 106.8273}
→ 201 {..., "geofence": "INSIDE"}
→ 422 bila di luar geofence sekolah
```

## Webhook keluar

Kelola di UI Webhooks: URL https, event, secret HMAC-SHA256
(header `X-MBG-Signature: sha256=...`), retry 5× + backoff queue, log +
retry manual + rotate secret. Event terpasang: `purchase.created`,
`purchase.approved`, `goods.received`, `production.started`,
`production.completed`, `qc.failed`, `batch.quarantined`, `batch.released`,
`delivery.dispatched`, `delivery.completed`, `delivery.failed`,
`complaint.created`, `invoice.created`, `recall.created`, `automation.fired`.
Event custom tersedia lewat automation rules (aksi `webhook`).
Tidak ada event `stock.updated` per-mutasi (terlalu berisik) — gunakan
laporan ledger/ekspor untuk sinkronisasi stok.

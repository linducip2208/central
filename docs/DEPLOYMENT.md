# Deployment Produksi

## Kebutuhan server

- PHP 8.3 + ekstensi: `pdo_mysql mbstring openssl tokenizer xml ctype json
  fileinfo gd exif bcmath zip curl`
- Composer 2, MySQL 8 (utf8mb4), supervisor (queue), cron (scheduler).
- Node TIDAK diperlukan (tidak ada build frontend; Tabler via CDN).

## Langkah deploy

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env   # lalu isi: APP_KEY, DB_*, MAIL_*, APP_URL
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force        # sekali saja (buat admin)
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Izin filesystem: `storage/` dan `bootstrap/cache/` writable oleh web user
(`chown -R www-data:www-data storage bootstrap/cache`).

## Scheduler (cron, tiap menit)

```cron
* * * * * cd /var/www/centralkitchen && php artisan schedule:run >> /dev/null 2>&1
```

## Queue worker (supervisor)

```ini
[program:mbg-queue]
command=php /var/www/centralkitchen/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=1
```

## Backup & rollback

- `php artisan mbg:backup --keep=7` (harian 02:00 via scheduler).
- Restore: `mysql db < storage/app/backups/db-*.sql`, lalu
  `php artisan migrate --force`.
- Rollback rilis: `git checkout <tag> && composer install --no-dev &&
  php artisan migrate --force` (migrasi hanya maju; siapkan snapshot DB
  sebelum deploy besar).

## Checklist produksi

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] APP_KEY terisi, `.env` tidak di-commit
- [ ] config/route/view cache aktif
- [ ] cron + supervisor jalan (`/health` → menu Kesehatan)
- [ ] `failed_jobs` dan webhook PENDING dimonitor
- [ ] Backup harian terverifikasi (coba restore berkala)

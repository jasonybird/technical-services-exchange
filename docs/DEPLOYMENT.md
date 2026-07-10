# Deployment Notes

This document records the explicit runtime requirements and server changes for Provider Exchange deployments.

## Required Runtime

- Linux server.
- Nginx.
- PHP-FPM with PHP `^8.3`; PHP 8.5 is acceptable for the current Laravel build.
- PHP extensions required by Laravel and current dependencies:
  - `ctype`
  - `curl`
  - `dom`
  - `fileinfo`
  - `filter`
  - `hash`
  - `mbstring`
  - `openssl`
  - `pdo`
  - `pdo_sqlite` for SQLite test deployments and the default automated test suite
  - `sqlite3` for SQLite-backed local tests
  - `gd` for generated image upload fixtures and image handling
  - `session`
  - `tokenizer`
  - `xml`
- Composer for clean production installs, or an uploaded `vendor/` tree for temporary live testing when Composer is unavailable.
- Node.js/npm only on build machines. The server can receive prebuilt Vite assets in `public/build`.
- Writable Laravel directories:
  - `storage/`
  - `bootstrap/cache/`
  - the SQLite database file for SQLite test deployments.
- Public attachment URLs require `php artisan storage:link`; the update script now runs it idempotently.
- Attachment policy environment variables:
  - `TSE_ATTACHMENT_DISK`, default `public`.
  - `TSE_ATTACHMENT_ROOT`, default `attachments`.
  - `TSE_ATTACHMENT_MAX_KB`, default `10240`.
  - `TSE_ATTACHMENT_MIME_TYPES`, comma-separated allowlist.
- Backup policy environment variables:
  - `TSE_BACKUP_DIR`, optional backup destination override.
  - `TSE_BACKUP_STORAGE`, default `1`, controls whether `storage/app` is archived with the database.
  - `TSE_BACKUP_BEFORE_UPDATE`, default `0`, runs `scripts/backup.sh` before `scripts/update.sh` when set to `1`.

## Local Test Requirements

- The default PHPUnit configuration uses an in-memory SQLite database, so whichever PHP runtime executes `php artisan test` must include `pdo_sqlite` and `sqlite3`.
- Upload tests also require `fileinfo` for MIME detection and `gd` for generated image fixtures.
- On Windows, `php -m` should list `PDO`, `pdo_sqlite`, `sqlite3`, `fileinfo`, and `gd`; otherwise the feature tests fail before reaching app code or upload assertions.
- WSL Ubuntu is acceptable for local verification when Windows PHP is missing extension support, as long as WSL PHP has the same extensions.

## Recommended Production Runtime

- Nginx.
- PHP-FPM 8.5 or current supported PHP version satisfying Composer constraints.
- MariaDB/MySQL or PostgreSQL for production data.
- Redis for cache, session, and queue once traffic or notifications grow.
- Supervisor or systemd units for Laravel queue workers.
- Cron entry for `php artisan schedule:run`.
- Shared/object storage before multi-server deployment.

## Queue Worker

The current ChristIT test deployment uses the database queue driver. That is acceptable for low-volume testing, but real notification, email, import, and image-processing work should run under a persistent worker.

Example systemd unit:

```ini
[Unit]
Description=Provider Exchange queue worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/sites/provider-exchange
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Reload workers after deployment with:

```bash
php artisan queue:restart
```

## Scheduler

Laravel's scheduler should be invoked once per minute by cron:

```cron
* * * * * cd /sites/provider-exchange && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

## PHP-FPM

Keep the app behind a dedicated PHP-FPM pool when production traffic increases. The pool should have:

- Its own Unix socket.
- A deploy user/group that can read the app and write `storage/` and `bootstrap/cache/`.
- `pm.max_children`, `pm.start_servers`, and spare server counts sized from observed memory usage.
- `request_terminate_timeout` high enough for uploads, but not high enough to pin workers indefinitely.
- `upload_max_filesize` and `post_max_size` aligned with `TSE_ATTACHMENT_MAX_KB`.

Do not change unrelated PHP-FPM sockets used by existing ChristIT sites when tuning this app.

## Redis Migration Plan

Before running multiple web nodes or high-volume alerting, move transient state out of the database:

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

After changing these values, run:

```bash
php artisan optimize:clear
php artisan queue:restart
```

Database-backed notifications can stay in the database because they are user-visible records. Queue transport, sessions, and cache should move first.

## Backups

Run an on-demand backup from the app directory:

```bash
bash scripts/backup.sh
```

The script supports SQLite, MySQL/MariaDB, and PostgreSQL based on `.env`. It writes a manifest beside the database backup. By default it also archives `storage/app`.

Run an update with a preflight backup:

```bash
TSE_BACKUP_BEFORE_UPDATE=1 bash scripts/update.sh
```

Recommended production posture:

- Store backups outside the app directory with `TSE_BACKUP_DIR`.
- Replicate backups off-server.
- Test restore steps before relying on the backup plan.
- Keep evidence attachments and profile uploads under a retention policy that matches the platform's dispute and audit needs.

## Health Check

Run:

```bash
bash scripts/health-check.sh
```

The check verifies Laravel boot, database/migration reachability, API and notification routes, writable cache/storage directories, storage symlink, and built Vite assets.

## Log Rotation

Laravel writes logs under `storage/logs`. Add a logrotate rule before real traffic:

```conf
/sites/provider-exchange/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

## Storage Scaling

Local public storage is acceptable for the first ChristIT test deployment. Before multi-server deployment:

- Move uploads/evidence to shared or object storage.
- Keep private evidence behind controller authorization instead of direct public URLs.
- Add malware scanning for uploaded evidence.
- Add thumbnail generation for large images.
- Add quota policy by user and work order.
- Keep attachment disk/path metadata stable so old evidence records continue to resolve after storage migration.

## Database Scaling

Phase 14A added indexes for high-use filters and timelines: jobs, profiles, quotes, work orders, messages, reviews, ratings, disputes, attachments, external imports, and notifications.

Next database reliability steps after real usage begins:

- Add cached reputation aggregates based on measured slow pages.
- Add query monitoring.
- Move from SQLite to MariaDB/MySQL or PostgreSQL before treating data as production-critical.
- Keep migrations additive where possible and back up before every schema-changing deployment.

## ChristIT Test Deployment

Target URL:

- `https://christit.com/tse`

Chosen deployment shape:

- Subdirectory deployment under the existing `christit.com` certificate.
- App code path: `/sites/provider-exchange`.
- Public web root exposed through Nginx should be `/sites/provider-exchange/public` only.
- Existing PHP-FPM socket should be reused: `/run/php/php8.5-fpm.sock`.
- Do not install or switch PHP 8.3 unless PHP 8.5 proves incompatible.
- Do not alter the existing generic PHP handling for the rest of `christit.com`.
- Add only a narrow `/tse` Nginx location and related PHP handler.

Initial test data/runtime:

- SQLite database at `/sites/provider-exchange/database/database.sqlite`.
- `APP_URL=https://christit.com`.
- `APP_BASE_PATH=` for the current Nginx setup, because Nginx passes `SCRIPT_NAME=/tse/index.php` and Laravel derives the request base from that.
- `ASSET_URL=https://christit.com/tse`.
- `SESSION_PATH=/tse`.
- `SESSION_DRIVER=database`.
- `CACHE_STORE=database`.
- `QUEUE_CONNECTION=database`.
- `MAIL_MAILER=log`.
- `TSE_ATTACHMENT_DISK=public`.
- `TSE_ATTACHMENT_ROOT=attachments`.
- `TSE_ATTACHMENT_MAX_KB=10240`.

## ChristIT Deployment Log

2026-07-10:

- Confirmed remote server uses Nginx and PHP 8.5-FPM.
- Confirmed `/run/php/php8.5-fpm.sock` exists.
- Confirmed `tse.christit.com` does not currently resolve and no certificate exists for it.
- Chose `https://christit.com/tse` as the first live test URL.
- Uploaded app source to `/home/jbird/provider-exchange-release`.
- Installed app source into `/sites/provider-exchange`.
- Remote Composer was not available in noninteractive SSH; uploaded the local `vendor/` tree for this temporary test deployment.
- Installed `php8.5-sqlite3` so the isolated SQLite test database can run under the existing PHP 8.5 runtime.
- Added a narrow `/tse` Nginx block to `/etc/nginx/sites-available/christit.com`.
- Nginx backups created by the helper include:
  - `/etc/nginx/sites-available/christit.com.provider-exchange-tse-20260710-115810.bak`
  - `/etc/nginx/sites-available/christit.com.provider-exchange-tse-20260710-120003.bak`
  - `/etc/nginx/sites-available/christit.com.provider-exchange-tse-20260710-120209.bak`
  - `/etc/nginx/sites-available/christit.com.provider-exchange-tse-20260710-121044.bak`
  - `/etc/nginx/sites-available/christit.com.provider-exchange-tse-20260710-121151.bak`
- Confirmed `nginx -t` passes after the `/tse` block was applied.
- Confirmed `https://christit.com/tse/` redirects to `https://christit.com/tse/jobs`.
- Confirmed `https://christit.com/tse/login` loads with a single-prefix form action.
- Confirmed seeded admin login succeeds and redirects to `https://christit.com/tse/dashboard`.
- No PHP-FPM version changes have been made.
- No database server changes have been made.
- Installed Composer on the remote server through Ubuntu packages so future updates can install PHP dependencies from `composer.lock`.
- Converted `/sites/provider-exchange` from the initial rsync test deployment into a Git checkout of `https://github.com/jasonybird/technical-services-exchange.git`.
- Preserved the previous rsync deployment at `/sites/provider-exchange.pre-git-20260710-125739`.
- Preserved the existing production `.env`, SQLite database, and Laravel `storage/` contents during the Git checkout conversion.
- Confirmed remote Git checkout at commit `b45a9ec`.
- Confirmed `bash scripts/update.sh` works from the remote Git checkout: Git pull, Composer install, npm install, Vite build, migrations, and Laravel cache rebuild completed successfully.
- Confirmed remote working tree is clean after normalizing copied `storage/` placeholder file modes.
- Confirmed authenticated smoke login with `admin@example.com` reaches `https://christit.com/tse/dashboard` and renders dashboard count content.

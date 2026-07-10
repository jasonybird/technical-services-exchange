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

## Local Test Requirements

- The default PHPUnit configuration uses an in-memory SQLite database, so whichever PHP runtime executes `php artisan test` must include `pdo_sqlite` and `sqlite3`.
- On Windows, `php -m` should list both `PDO` and `pdo_sqlite`; otherwise the feature tests fail before reaching app code with `could not find driver`.
- WSL Ubuntu is acceptable for local verification when Windows PHP is missing SQLite support, as long as WSL PHP has `pdo_sqlite` and `sqlite3`.

## Recommended Production Runtime

- Nginx.
- PHP-FPM 8.5 or current supported PHP version satisfying Composer constraints.
- MariaDB/MySQL or PostgreSQL for production data.
- Redis for cache, session, and queue once traffic or notifications grow.
- Supervisor or systemd units for Laravel queue workers.
- Cron entry for `php artisan schedule:run`.
- Shared/object storage before multi-server deployment.

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

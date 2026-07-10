# Install And Update

Provider Exchange is a Laravel application. A server install needs PHP-FPM, Nginx, Composer, Node/npm for asset builds, and a database.

## Quick Ubuntu Install

From a fresh Ubuntu host:

```bash
sudo APP_URL=https://example.com \
    APP_DIR=/var/www/technical-services-exchange \
    bash -c "$(curl -fsSL https://raw.githubusercontent.com/jasonybird/technical-services-exchange/main/scripts/install-ubuntu.sh)"
```

The installer defaults to SQLite for a small test deployment. For production, use MariaDB/MySQL or PostgreSQL and set the database values in `.env` before running migrations.

## Update Existing Install

From the app directory:

```bash
bash scripts/update.sh
```

To create a database/storage backup immediately before updating:

```bash
TSE_BACKUP_BEFORE_UPDATE=1 bash scripts/update.sh
```

Or from anywhere:

```bash
APP_DIR=/var/www/technical-services-exchange bash /var/www/technical-services-exchange/scripts/update.sh
```

The preferred working loop for this project is:

1. Make and test changes locally.
2. Commit and push to GitHub.
3. SSH to the server and run `bash scripts/update.sh` from the deployed Git checkout.
4. Smoke test the live URL after the update completes.

## Backups And Health Checks

Create an on-demand backup from the app directory:

```bash
bash scripts/backup.sh
```

Backups default to `storage/app/backups`. Set `TSE_BACKUP_DIR=/path/to/backups` to store them elsewhere. Set `TSE_BACKUP_STORAGE=0` to back up only the database.

Run a deployment health check from the app directory:

```bash
bash scripts/health-check.sh
```

## Important Notes

- The installer is a first-pass Ubuntu/Nginx helper, not a universal hosting panel installer.
- Local and CI test runners need PHP SQLite and upload-test support: `pdo_sqlite`, `sqlite3`, `fileinfo`, and `gd`.
- Public attachment serving needs `php artisan storage:link`; the checked-in update script runs this automatically.
- Tune upload policy with `TSE_ATTACHMENT_DISK`, `TSE_ATTACHMENT_ROOT`, `TSE_ATTACHMENT_MAX_KB`, and `TSE_ATTACHMENT_MIME_TYPES`.
- Tune backup policy with `TSE_BACKUP_DIR`, `TSE_BACKUP_STORAGE`, and optional `TSE_BACKUP_BEFORE_UPDATE=1`.
- Review `.env` before production use.
- Configure real mail before inviting users.
- Move sessions, cache, and queues to Redis before serious traffic.
- Move uploads to shared or object storage before multi-server deployment.
- Use a real database server before treating the platform as production data.

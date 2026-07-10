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

Or from anywhere:

```bash
APP_DIR=/var/www/technical-services-exchange bash /var/www/technical-services-exchange/scripts/update.sh
```

## Important Notes

- The installer is a first-pass Ubuntu/Nginx helper, not a universal hosting panel installer.
- Local and CI test runners need PHP SQLite support: `pdo_sqlite` and `sqlite3`.
- Review `.env` before production use.
- Configure real mail before inviting users.
- Move sessions, cache, and queues to Redis before serious traffic.
- Move uploads to shared or object storage before multi-server deployment.
- Use a real database server before treating the platform as production data.

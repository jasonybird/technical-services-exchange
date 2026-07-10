#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
BACKUP_DIR="${TSE_BACKUP_DIR:-$APP_DIR/storage/app/backups}"
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"

cd "$APP_DIR"
mkdir -p "$BACKUP_DIR"

env_value() {
    local key="$1"
    local fallback="${2:-}"
    if [ -f .env ]; then
        local line
        line="$(grep -E "^${key}=" .env | tail -n 1 || true)"
        if [ -n "$line" ]; then
            local value="${line#*=}"
            value="${value%\"}"
            value="${value#\"}"
            printf '%s' "$value"
            return
        fi
    fi
    printf '%s' "$fallback"
}

DB_CONNECTION="$(env_value DB_CONNECTION sqlite)"
DB_DATABASE="$(env_value DB_DATABASE "$APP_DIR/database/database.sqlite")"
DB_HOST="$(env_value DB_HOST 127.0.0.1)"
DB_PORT="$(env_value DB_PORT 3306)"
DB_USERNAME="$(env_value DB_USERNAME root)"
DB_PASSWORD="$(env_value DB_PASSWORD '')"

MANIFEST="$BACKUP_DIR/tse-backup-$STAMP.manifest.txt"
{
    echo "Technical Services Exchange backup"
    echo "created_utc=$STAMP"
    echo "app_dir=$APP_DIR"
    echo "git_commit=$(git rev-parse --short HEAD 2>/dev/null || echo unknown)"
    echo "db_connection=$DB_CONNECTION"
} > "$MANIFEST"

case "$DB_CONNECTION" in
    sqlite)
        if [ ! -f "$DB_DATABASE" ]; then
            echo "SQLite database not found: $DB_DATABASE" >&2
            exit 1
        fi
        DB_BACKUP="$BACKUP_DIR/database-$STAMP.sqlite"
        cp "$DB_DATABASE" "$DB_BACKUP"
        echo "database_backup=$DB_BACKUP" >> "$MANIFEST"
        ;;
    mysql|mariadb)
        command -v mysqldump >/dev/null 2>&1 || { echo "mysqldump is required for $DB_CONNECTION backups" >&2; exit 1; }
        DB_BACKUP="$BACKUP_DIR/database-$STAMP.sql.gz"
        MYSQL_PWD="$DB_PASSWORD" mysqldump --single-transaction --quick --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" "$DB_DATABASE" | gzip > "$DB_BACKUP"
        echo "database_backup=$DB_BACKUP" >> "$MANIFEST"
        ;;
    pgsql)
        command -v pg_dump >/dev/null 2>&1 || { echo "pg_dump is required for pgsql backups" >&2; exit 1; }
        DB_BACKUP="$BACKUP_DIR/database-$STAMP.sql.gz"
        PGPASSWORD="$DB_PASSWORD" pg_dump --host="$DB_HOST" --port="${DB_PORT:-5432}" --username="$DB_USERNAME" "$DB_DATABASE" | gzip > "$DB_BACKUP"
        echo "database_backup=$DB_BACKUP" >> "$MANIFEST"
        ;;
    *)
        echo "Unsupported DB_CONNECTION for backup: $DB_CONNECTION" >&2
        exit 1
        ;;
esac

if [ "${TSE_BACKUP_STORAGE:-1}" = "1" ] && [ -d storage/app ]; then
    STORAGE_BACKUP="$BACKUP_DIR/storage-app-$STAMP.tar.gz"
    tar --exclude="$BACKUP_DIR" --exclude="storage/app/backups" -czf "$STORAGE_BACKUP" -C "$APP_DIR" storage/app
    echo "storage_backup=$STORAGE_BACKUP" >> "$MANIFEST"
fi

echo "manifest=$MANIFEST"
echo "Backup complete."

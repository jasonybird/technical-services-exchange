#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"

cd "$APP_DIR"

if [ "${TSE_BACKUP_BEFORE_UPDATE:-0}" = "1" ]; then
    bash scripts/backup.sh
fi

git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan optimize

echo "Technical Services Exchange updated in $APP_DIR"

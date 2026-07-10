#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"

cd "$APP_DIR"

fail() {
    echo "FAIL: $1" >&2
    exit 1
}

pass() {
    echo "OK: $1"
}

[ -f artisan ] || fail "artisan not found in $APP_DIR"
[ -f .env ] || fail ".env is missing"
[ -d storage ] || fail "storage directory is missing"
[ -w storage ] || fail "storage directory is not writable"
[ -d bootstrap/cache ] || fail "bootstrap/cache directory is missing"
[ -w bootstrap/cache ] || fail "bootstrap/cache directory is not writable"

php artisan about --only=environment >/dev/null || fail "Laravel cannot boot"
pass "Laravel boots"

php artisan migrate:status >/dev/null || fail "Database connection or migrations failed"
pass "Database and migrations are reachable"

php artisan route:list --path=api/v1 >/dev/null || fail "API routes are not available"
pass "API routes are available"

php artisan route:list --name=notifications >/dev/null || fail "Notification routes are not available"
pass "Notification routes are available"

if [ -L public/storage ] || [ -d public/storage ]; then
    pass "public storage link exists"
else
    fail "public/storage link is missing; run php artisan storage:link"
fi

if [ -d public/build ]; then
    pass "Vite build assets exist"
else
    fail "public/build is missing; run npm run build"
fi

echo "Health check complete."

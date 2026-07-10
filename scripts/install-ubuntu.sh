#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/technical-services-exchange}"
REPO_URL="${REPO_URL:-https://github.com/jasonybird/technical-services-exchange.git}"
APP_URL="${APP_URL:-http://localhost}"
APP_BASE_PATH="${APP_BASE_PATH:-}"
ASSET_URL="${ASSET_URL:-$APP_URL}"
DB_CONNECTION="${DB_CONNECTION:-sqlite}"
DB_DATABASE="${DB_DATABASE:-$APP_DIR/database/database.sqlite}"
PHP_FPM_SOCKET="${PHP_FPM_SOCKET:-/run/php/php8.5-fpm.sock}"
NGINX_SITE_NAME="${NGINX_SITE_NAME:-technical-services-exchange}"
WEB_USER="${WEB_USER:-www-data}"

if [ "$(id -u)" -ne 0 ]; then
    echo "Run as root: sudo APP_URL=https://example.com bash scripts/install-ubuntu.sh" >&2
    exit 1
fi

apt-get update
apt-get install -y \
    git unzip curl ca-certificates nginx composer \
    php-cli php-fpm php-curl php-mbstring php-xml php-zip php-sqlite3 php-mysql \
    nodejs npm

mkdir -p "$(dirname "$APP_DIR")"

if [ -d "$APP_DIR/.git" ]; then
    git -C "$APP_DIR" pull --ff-only
else
    git clone "$REPO_URL" "$APP_DIR"
fi

cd "$APP_DIR"

composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build

if [ ! -f .env ]; then
    cp .env.example .env
fi

set_env() {
    local key="$1"
    local value="$2"
    if grep -q "^${key}=" .env; then
        sed -i "s#^${key}=.*#${key}=${value}#" .env
    else
        printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
}

set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "$APP_URL"
set_env APP_BASE_PATH "$APP_BASE_PATH"
set_env ASSET_URL "$ASSET_URL"
set_env DB_CONNECTION "$DB_CONNECTION"
set_env DB_DATABASE "$DB_DATABASE"
set_env SESSION_DRIVER database
set_env SESSION_PATH "${APP_BASE_PATH:-/}"
set_env CACHE_STORE database
set_env QUEUE_CONNECTION database
set_env MAIL_MAILER log

if [ "$DB_CONNECTION" = "sqlite" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

php artisan key:generate --force
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan optimize

chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache
if [ "$DB_CONNECTION" = "sqlite" ]; then
    chown "$WEB_USER:$WEB_USER" "$DB_DATABASE"
fi

cat > "/etc/nginx/sites-available/$NGINX_SITE_NAME" <<NGINX
server {
    listen 80;
    server_name _;
    root $APP_DIR/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$PHP_FPM_SOCKET;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }
}
NGINX

ln -sf "/etc/nginx/sites-available/$NGINX_SITE_NAME" "/etc/nginx/sites-enabled/$NGINX_SITE_NAME"
nginx -t
systemctl reload nginx

echo "Installed Technical Services Exchange at $APP_DIR"
echo "URL: $APP_URL"

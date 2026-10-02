#!/bin/sh
# Dijalankan setiap container menyala.
set -e
cd /var/www/html

# APP_KEY wajib ada. Kalau belum diisi di environment, buat otomatis.
if [ -z "$APP_KEY" ]; then
    export APP_KEY="$(php artisan key:generate --show --no-interaction)"
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Buat ulang database demo dari nol, lalu isi data dummy + akun demo.
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh --seed --force --no-interaction

# Cache konfigurasi, route, dan view supaya lebih cepat.
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache database

exec apache2-foreground

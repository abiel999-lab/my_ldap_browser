#!/bin/sh
set -e

cd /var/www/html

echo "===> Removing local .env to force Kubernetes env usage..."
rm -f .env || true

echo "===> Waiting for startup..."
sleep 5

echo "===> Ensuring Laravel directories..."
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

chmod -R 775 storage bootstrap/cache || true

echo "===> Clearing old cache..."
php artisan optimize:clear || true

echo "===> Running migrations..."
php artisan migrate --force || true

echo "===> Caching config, routes, views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "===> Starting Laravel server..."
exec php artisan serve --host=0.0.0.0 --port=8000

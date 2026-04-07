#!/bin/bash

php artisan lang:generate
php artisan view:cache
php artisan route:cache
php artisan config:cache

php artisan filament:optimize-clear
php artisan filament:optimize
php artisan icons:cache
# php artisan octane:cache #command ini error

# Baris ini adalah KUNCINYA.
# 'exec' akan menggantikan proses skrip ini dengan perintah dari CMD Dockerfile.
# Dalam kasus ini, ia akan menjalankan:
php artisan octane:start --host=0.0.0.0 --port=8000
# exec "$@"

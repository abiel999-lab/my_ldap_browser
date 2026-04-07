#!/bin/bash

php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan filament:optimize-clear
php artisan octane:reload

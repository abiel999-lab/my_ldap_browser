#!/bin/bash

if [ "$ENABLE_SCHEDULER" = "true" ]; then
    touch /var/www/html/storage/logs/cron.log
    echo "* * * * * root /usr/local/bin/php /var/www/html/artisan schedule:run >> /var/www/html/storage/logs/cron.log 2>&1" \
        > /etc/cron.d/laravel-scheduler
    chmod 0644 /etc/cron.d/laravel-scheduler
    printenv >> /etc/environment
    cron
fi

# Start supervisord
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

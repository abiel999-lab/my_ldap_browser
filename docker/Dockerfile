FROM petrahub.petra.ac.id/eksternal/composer as dependency
FROM petrahub.petra.ac.id/eksternal/phpfranken:8.4

# Set environment variables to prevent interactive prompts during build
ENV SHELL=/bin/bash \
    DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1

# PHP Config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN sed -E -i -e 's/max_execution_time = 30/max_execution_time = 10800/' "$PHP_INI_DIR/php.ini" \
    && sed -E -i -e 's/memory_limit = 128M/memory_limit = 2048M/' "$PHP_INI_DIR/php.ini" \
    && sed -E -i -e 's/post_max_size = 8M/post_max_size = 100M/' "$PHP_INI_DIR/php.ini" \
    && sed -E -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 10M/' "$PHP_INI_DIR/php.ini"

# Composer
COPY --from=dependency /usr/bin/composer /usr/bin/composer
# working directory
WORKDIR /app

# Project App
COPY composer.json /app
COPY composer.lock /app

RUN composer install --no-interaction --no-dev --no-autoloader --no-scripts

# Project App, COMMENT jika ingin coba di local dengan docker compose up
COPY . /app

# Hapus folder tidak perlu lagi
# Set Permission
RUN npm ci; \
    npm run build; \
    rm -rf node_modules; \
    rm -r /app/.gitlab ; \
    rm -r /app/.vscode ; \
    rm -r /app/.gitlab-ci.yml ; \
    rm -r /app/README.md ; \
    rm -r /app/docker-compose.yml ; \
    rm -r /app/Dockerfile.local ; \
    rm -r /app/k8s ; \
    chown ppsi:www-data -R . ; \
    chmod 775 -R storage bootstrap/cache ; \
    chmod g+s -R storage bootstrap/cache ; \
    chmod 775 docker/init.sh ; \
    composer dump-autoload; \
    php artisan octane:install --server=frankenphp

# Expose the port Octane will run on
EXPOSE 8000

# Set the entrypoint script to run initialization tasks
ENTRYPOINT ["docker/init.sh"]
# The command to run the application server
# CMD ["php", "artisan", "octane:start", "--host=0.0.0.0", "--port=8000"]




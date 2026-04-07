FROM petrahub.petra.ac.id/eksternal/composer as dependency
FROM petrahub.petra.ac.id/eksternal/php-ppsi:8.4-cli


# PHP Configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN sed -E -i -e 's/max_execution_time = 30/max_execution_time = 10800/' "$PHP_INI_DIR/php.ini" \
    && sed -E -i -e 's/memory_limit = 128M/memory_limit = 2048M/' "$PHP_INI_DIR/php.ini"

# Composer
COPY --from=dependency /usr/bin/composer /usr/bin/composer

# Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/laravel-worker.conf

# working directory
WORKDIR /var/www/html

# Project App
COPY composer.json /var/www/html
COPY composer.lock /var/www/html

RUN composer install --no-autoloader --no-dev

# Project App
COPY . /var/www/html

# Hapus folder tidak perlu lagi
# Set Permission
RUN rm -r /var/www/html/.gitlab ; \
    rm -r /var/www/html/.vscode ; \
    rm -r /var/www/html/.gitlab-ci.yml ; \
    rm -r /var/www/html/README.md ; \
    rm -r /var/www/html/k8s ; \
    rm -r /var/www/html/Dockerfile.local ; \
    rm -r /var/www/html/docker-compose.yaml ; \
    chown ppsi:www-data -R . ; \
    chmod 775 -R storage bootstrap/cache ; \
    chmod g+s -R storage bootstrap/cache

RUN composer dump-autoload

CMD ["bash", "docker/worker.sh"]


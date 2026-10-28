# syntax=docker/dockerfile:1

FROM dunglas/frankenphp:1-php8.2 AS base

RUN apt-get update && \
    apt-get install -y --no-install-recommends cron tzdata && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

FROM base AS development

RUN install-php-extensions zip pdo_pgsql redis intl xdebug bcmath excimer @composer && \
    mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    echo "xdebug.mode=develop,debug" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "xdebug.client_port=9003" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "xdebug.log=/tmp/xdebug.log" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "xdebug.start_with_request=yes" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "xdebug.client_host=host.docker.internal" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "* * * * * /usr/local/bin/php /var/www/html/bin/console app:tasks-executor > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data && \
    echo "0 * * * * /usr/local/bin/php /var/www/html/bin/console app:statistics-collector > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data

COPY --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony

COPY --chown=www-data:www-data . .

FROM base AS production

RUN install-php-extensions \
        pdo_pgsql \
        redis \
        intl \
        bcmath \
        excimer \
        opcache \
        zip \
        @composer \
    && setcap -r /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /config/caddy /data/caddy

COPY --chown=www-data:www-data composer.json composer.lock ./

RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-autoloader

COPY docker/configuration/Caddyfile /etc/frankenphp/Caddyfile
COPY --chown=www-data:www-data docker/scripts/production/entrypoint.sh /var/www/html/entrypoint.sh
COPY --chown=www-data:www-data . .

RUN composer dump-autoload --no-dev --no-scripts --classmap-authoritative && \
    composer dump-env prod && \
    echo "0 0 * * * /usr/local/bin/php /var/www/html/bin/console app:shortcut-cleanup > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data && \
    echo "0 0 * * * /usr/local/bin/php /var/www/html/bin/console app:reports-summary > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data && \
    echo "0 0 * * * /usr/local/bin/php /var/www/html/bin/console app:api-keys-rotation > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data && \
    chmod +x /var/www/html/entrypoint.sh && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    printf '%s\n' \
      'realpath_cache_ttl = 600' \
      'expose_php = Off' \
      'session.use_strict_mode = 1' \
      'session.cookie_secure = 1' \
      'session.name = __SECURE-PHPSESSID' \
      'session.cookie_httponly = 1' \
      'session.cookie_samesite = Strict' \
      'opcache.revalidate_freq = 60' \
      'opcache.validate_timestamps = 0' \
      'opcache.memory_consumption = 256' \
      'opcache.interned_strings_buffer = 16' \
      'opcache.max_accelerated_files = 20000' \
    > "$PHP_INI_DIR/conf.d/zz-custom.ini"

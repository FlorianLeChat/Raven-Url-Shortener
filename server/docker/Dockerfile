# syntax=docker/dockerfile:1

FROM dunglas/frankenphp:1-php8.2 AS base

RUN apt-get update && \
    apt-get install -y --no-install-recommends tzdata && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

FROM base AS development

RUN install-php-extensions zip pdo_pgsql redis intl xdebug bcmath excimer @composer && \
    mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    echo "xdebug.mode=develop,debug" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "xdebug.client_port=9003" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "xdebug.log=/tmp/xdebug.log" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "xdebug.start_with_request=yes" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
    echo "xdebug.client_host=host.docker.internal" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"

COPY --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony

COPY --chown=www-data:www-data . .

FROM base AS application

RUN install-php-extensions \
        pdo_pgsql \
        redis \
        intl \
        bcmath \
        excimer \
        opcache \
        zip \
    && setcap -r /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /config/caddy /data/caddy

COPY --chown=www-data:www-data composer.json composer.lock ./

RUN --mount=type=bind,from=composer:2,source=/usr/bin/composer,target=/usr/local/bin/composer \
    --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-autoloader

COPY docker/configuration/Caddyfile /etc/frankenphp/Caddyfile
COPY --chown=www-data:www-data . .

RUN --mount=type=bind,from=composer:2,source=/usr/bin/composer,target=/usr/local/bin/composer \
    composer dump-autoload --no-dev --no-scripts --classmap-authoritative && \
    composer dump-env prod && \
    composer run-script --no-dev post-install-cmd && \
    rm -rf var && \
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

FROM node:22-alpine AS documentation

WORKDIR /documentation

RUN apk add --no-cache gettext

COPY .env openapi.yaml ./

RUN export $(grep -E \
        '^(VERSION|RATE_LIMIT_ENABLED|RATE_LIMIT_READ_API|RATE_LIMIT_WRITE_API)=' \
        .env | xargs) && \
    envsubst '${VERSION} ${RATE_LIMIT_ENABLED} ${RATE_LIMIT_READ_API} ${RATE_LIMIT_WRITE_API}' \
        < openapi.yaml > openapi.resolved.yaml && \
    rm .env && \
    npx --yes @redocly/cli@2.78.0 lint openapi.resolved.yaml && \
    npx --yes @redocly/cli@2.78.0 build-docs openapi.resolved.yaml \
        --inlineBundle \
        --title "Raven Url Shortener API" \
        --output index.html

FROM application AS production

COPY --from=documentation --chown=www-data:www-data /documentation/index.html public/docs/index.html

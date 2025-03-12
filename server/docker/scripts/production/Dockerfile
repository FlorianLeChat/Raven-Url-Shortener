# syntax=docker/dockerfile:1

# Use an customized image of PHP
# https://hub.docker.com/_/php
FROM php:fpm-alpine

# Install system dependencies
RUN apk add --no-cache supercronic tzdata

# Install some PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions zip pdo_mysql pdo_pgsql redis opcache intl bcmath excimer @composer

# Set the working directory to the website files
WORKDIR /var/www/html

# Add some cronjobs for Symfony custom commands
# https://github.com/webdevops/Dockerfile/issues/280#issuecomment-1311681838
RUN echo "0 0 * * * /usr/local/bin/php /var/www/html/bin/console app:shortcut-cleanup > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data && \
	echo "0 0 * * * /usr/local/bin/php /var/www/html/bin/console app:reports-summary > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data

# Add wait script to wait for other services to be ready
ADD --chmod=0755 https://github.com/ufoscout/docker-compose-wait/releases/latest/download/wait /wait

# Copy all files to the working directory
COPY --chown=www-data:www-data . .

# Use the default production PHP configuration with some modifications
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
	# Cache a filesystem path to real path mapping for 10 minutes
	sed -i "s/^;realpath_cache_ttl=120/realpath_cache_ttl=600/g" "$PHP_INI_DIR/php.ini" && \
	# Don't expose PHP version to HTTP response headers
	sed -i "s/expose_php=On/expose_php=Off/g" "$PHP_INI_DIR/php.ini" && \
	# Increase default OPcache settings to improve performance
	sed -i "s/;opcache.revalidate_freq=2/opcache.revalidate_freq=60/g" "$PHP_INI_DIR/php.ini" && \
	sed -i "s/;opcache.validate_timestamps=1/opcache.validate_timestamps=0/g" "$PHP_INI_DIR/php.ini" && \
	sed -i "s/;opcache.memory_consumption=128/opcache.memory_consumption=256/g" "$PHP_INI_DIR/php.ini" && \
	sed -i "s/;opcache.interned_strings_buffer=8/opcache.interned_strings_buffer=16/g" "$PHP_INI_DIR/php.ini" && \
	sed -i "s/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=20000/g" "$PHP_INI_DIR/php.ini"

# Change current user to www-data
USER www-data

# Make the entrypoint script executable
RUN chmod +x ./docker/scripts/production/entrypoint.sh
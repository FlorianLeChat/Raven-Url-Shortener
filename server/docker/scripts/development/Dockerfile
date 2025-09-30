# syntax=docker/dockerfile:1

# Use an customized image of PHP
# https://hub.docker.com/_/php
FROM php:8.4-apache

# Install system and PHP extensions
# https://xdebug.org/docs/all_settings#mode
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN RUN apt update && apt install -y cron tzdata && apt clean && \
	install-php-extensions zip pdo_mysql pdo_pgsql redis intl xdebug bcmath excimer @composer && \
	echo "xdebug.mode=develop,debug" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
	echo "xdebug.client_port=9003" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
	echo "xdebug.log=/tmp/xdebug.log" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
	echo "xdebug.start_with_request=yes" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" && \
	echo "xdebug.client_host=host.docker.internal" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"

# Set the working directory to the website files
WORKDIR /var/www/html

# Add some cronjobs for Symfony custom commands
# https://github.com/webdevops/Dockerfile/issues/280#issuecomment-1311681838
RUN echo "0 0 * * * /usr/local/bin/php /var/www/html/bin/console app:shortcut-cleanup > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data && \
	echo "0 0 * * * /usr/local/bin/php /var/www/html/bin/console app:reports-summary > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data && \
	echo "0 0 * * * /usr/local/bin/php /var/www/html/bin/console app:api-keys-rotation > /dev/null 2>&1" >> /var/spool/cron/crontabs/www-data

# Copy all files to the working directory
COPY --chown=www-data:www-data . .

# Install Symfony CLI
COPY --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony

# Use the default development PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Change current user to www-data
USER www-data

# Make the entrypoint script executable
RUN chmod +x ./docker/scripts/development/entrypoint.sh
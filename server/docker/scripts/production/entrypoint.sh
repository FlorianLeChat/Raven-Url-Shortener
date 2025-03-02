#!/bin/sh

# Wait for external services to be ready
/wait

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Clear Symfony application cache
/usr/local/bin/php bin/console cache:clear

# Dump environment variables for production
composer dump-env prod

# Create database and update schema
/usr/local/bin/php bin/console doctrine:database:create --no-interaction --if-not-exists
/usr/local/bin/php bin/console doctrine:schema:update --force

# Run cron service in background
supercronic /etc/crontabs/www-data &

# Start PHP-FPM daemon
php-fpm
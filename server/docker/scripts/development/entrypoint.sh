#!/bin/sh

# Install Composer dependencies
composer install

# Create database and update schema
/usr/local/bin/php bin/console doctrine:database:create --no-interaction --if-not-exists
/usr/local/bin/php bin/console doctrine:schema:update --force

# Run cron service in background
supercronic /etc/crontabs/www-data &

# Clear previous Symfony local server cache
rm -rf ~/.symfony5/

# Start Symfony server to listen on all interfaces
symfony server:start --allow-all-ip
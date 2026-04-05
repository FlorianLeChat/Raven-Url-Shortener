#!/bin/sh

/usr/local/bin/php bin/console doctrine:database:create --no-interaction --if-not-exists
/usr/local/bin/php bin/console doctrine:schema:update --force

frankenphp run --config /etc/frankenphp/Caddyfile

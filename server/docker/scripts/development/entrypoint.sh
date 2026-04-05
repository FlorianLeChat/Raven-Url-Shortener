#!/bin/sh

composer install --no-interaction

/usr/local/bin/php bin/console doctrine:database:create --no-interaction --if-not-exists
/usr/local/bin/php bin/console doctrine:schema:update --force

symfony server:start --allow-all-ip

#!/usr/bin/env bash

echo "--- Container starting ---"

mkdir -p /var/www/var/log/php
touch /var/www/var/log/php/error.log

# Toggle Xdebug based on env variable
if [ "$XDEBUG_ENABLED" = "true" ] && [ "$APP_DEBUG" = "1" ]; then
    if [ ! -f /usr/local/etc/php/conf.d/xdebug.ini ] && [ -f /usr/local/etc/php/src/xdebug.ini ]; then
        cp /usr/local/etc/php/src/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
    fi
else
    if [ -f /usr/local/etc/php/conf.d/xdebug.ini ]; then
        rm /usr/local/etc/php/conf.d/xdebug.ini
    fi
fi

if [ "$APP_ENV" = "prod" ] || [ "$APP_ENV" = "stage" ]; then
  echo "Composer starting $APP_ENV"
  export COMPOSER_ALLOW_SUPERUSER=1
  composer start
  composer apply-migrations
fi

echo "Fixing permissions"
if [ -d /var/www/var/cache ]; then
    chown -R www-data:www-data /var/www/var/cache
    chown -R www-data:www-data /var/www/var/log
    chown -R www-data:www-data /var/www/public
    chown -R www-data:www-data /var/www/var/log/php
    chmod -R 777 /var/www/var/cache /var/www/var/log /var/www/var/log/php
    if [ "$APP_ENV" = "dev" ]; then
        setfacl -dRLm o::rwx /var/www/var/cache /var/www/var/log /var/www/var/log/php
    fi
fi

echo "--- Container started ---"
exec "$@"

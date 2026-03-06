#!/bin/bash

cd /app

echo "Running database migrations..."
if ! php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration; then
    echo "Failed to run migrations. Exiting..."
    exit 1
fi

echo "Clearing and warming up Symfony cache..."
php bin/console cache:clear --no-warmup --env=prod
php bin/console cache:warmup --env=prod

echo "Setting permissions for var/cache and var/log directories..."
chmod -R 775 var/cache var/log

echo "Starting supervisor to manage processes..."
exec /usr/bin/supervisord

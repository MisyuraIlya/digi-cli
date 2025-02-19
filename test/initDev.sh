#!/bin/bash

# Run Symfony commands to initialize the application



composer install --no-dev --optimize-autoloader

composer update

composer dump-env dev

# Create the database
php bin/console doctrine:database:create --if-not-exists || true

# Generate a migration
php bin/console make:migration --no-interaction || true

# Apply migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

php bin/console cache:clear

# Start PHP-FPM in the foreground
php-fpm
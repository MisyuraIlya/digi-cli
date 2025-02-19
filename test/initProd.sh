#!/bin/bash

# Run Symfony commands to initialize the application

# Create the database
php bin/console doctrine:database:create --if-not-exists || true

# Generate a migration
php bin/console make:migration --no-interaction || true

# Apply migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

composer dump-env prod

php bin/console cache:clear

composer install --no-dev --optimize-autoloader
# Start PHP-FPM in the foreground
php-fpm
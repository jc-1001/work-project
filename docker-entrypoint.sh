#!/bin/bash
composer install --no-interaction --optimize-autoloader --ignore-platform-reqs
php artisan config:cache
# php artisan route:cache
php artisan view:cache
chown -R www-data /var/www/html/storage
php artisan storage:link
/usr/bin/supervisord -n

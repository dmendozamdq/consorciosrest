#!/bin/bash

cp -R /var/www/tmp/. /var/www/html/
yes | cp /var/www/tmp/vendorEdit/DatabaseUserProvider.php /var/www/html/vendor/laravel/framework/src/Illuminate/Auth/
yes | cp /var/www/tmp/vendorEdit/EloquentUserProvider.php /var/www/html/vendor/laravel/framework/src/Illuminate/Auth/
yes | cp /var/www/tmp/vendorEdit/PasswordBroker.php /var/www/html/vendor/laravel/framework/src/Illuminate/Auth/Passwords
chown -R www-data:www-data /var/www/html
chmod 775 /var/www
chmod 777 /var/www/html/storage
php artisan config:cache

exec "$@"
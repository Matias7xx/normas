#!/bin/bash

# Ajustar permissões
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Iniciar PHP-FPM
exec php-fpm
#!/bin/bash

# Verificar se tabelas já existem
if php artisan migrate:status | grep -q "Migration table not found"; then
    echo "Primeira execução - criando tabelas..."
    php artisan migrate --force
    php artisan db:seed --force
else
    echo "Banco já configurado - pulando migrations."
fi

exec php-fpm
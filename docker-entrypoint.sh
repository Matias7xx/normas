#!/bin/bash
set -e

echo "Iniciando aplicação..."

# Aguardar banco de dados estar disponível
echo "Aguardando banco de dados..."
#until pg_isready -h db -p 5432 -U postgres >/dev/null 2>&1; do
#    echo "💤 Banco não disponível, aguardando 3 segundos..."
#    sleep 3
# done

echo "Banco de dados disponível!"

# Executar migrações
echo "Verificando status das migrações..."

# Verificar se a tabela migrations existe
if php artisan migrate:status 2>&1 | grep -q "Migration table not found"; then
    echo "Primeira execução - criando estrutura do banco..."
    php artisan migrate --force
    php artisan db:seed --force
    echo "Banco de dados inicializado com sucesso!"
else
    echo "Verificando se há migrações pendentes..."
    # Verificar se há migrações pendentes
    if php artisan migrate:status | grep -q "Pending"; then
        echo "Executando migrações pendentes..."
        php artisan migrate --force
        echo "Migrações executadas!"
    else
        echo "Banco já está atualizado!"
    fi
fi

php artisan migrate --force

# Limpar e otimizar caches
echo "Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Otimizar para produção (se não for ambiente local)
if [ "$APP_ENV" != "local" ]; then
    echo "Otimizando para produção..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Garantir que o storage link existe
echo "Verificando storage link..."
if [ ! -L public/storage ]; then
    echo "Criando storage link..."
    php artisan storage:link --no-interaction
else
    echo "Storage link já existe, pulando..."
fi

# Ajustar permissões finais
echo "Ajustando permissões..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache

# Garantir que public/storage tenha as permissões corretas se existir
if [ -L public/storage ]; then
    chown -h www-data:www-data public/storage
fi

# Composer install
echo "Verificando dependências..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "Aplicação pronta! Iniciando Apache..."

# Iniciar Apache
exec apache2-foreground

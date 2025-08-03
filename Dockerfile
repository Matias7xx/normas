FROM php:8.2-apache

# Instalar dependências básicas
RUN apt-get update && apt-get install -y \
    sudo \
    nano \
    cron \
    libpng-dev \
    libjpeg-dev \
    libpq-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    wget \
    dnsutils \
    iputils-ping \
    telnet \
    postgresql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql zip opcache bcmath

# Instale uma versão mais recente do Node.js (v22)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# Habilite o módulo de reescrita do Apache
RUN a2enmod rewrite

# Configurar o Apache para servir o Laravel
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Configurar o tamanho máximo de upload e de post criando o arquivo customizado
RUN echo "upload_max_filesize = 1G\npost_max_size = 1G\nmemory_limit = 1024M\nmax_execution_time = 1200" > /usr/local/etc/php/conf.d/uploads.ini \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

# Configurações específicas para curl e resolução DNS
RUN echo "default_socket_timeout = 60\nauto_detect_line_endings = On" >> /usr/local/etc/php/conf.d/network.ini

# Configuração do OPcache para produção
RUN echo "opcache.enable=1\nopcache.memory_consumption=256\nopcache.interned_strings_buffer=16\nopcache.max_accelerated_files=20000\nopcache.validate_timestamps=0\nopcache.save_comments=1\nopcache.fast_shutdown=1" > /usr/local/etc/php/conf.d/opcache.ini

# Instale o Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Atualizar o Composer para a versão mais recente
RUN composer self-update

# Definir diretório
WORKDIR /var/www/html

# Copie o conteúdo do projeto para o diretório de trabalho no container
COPY . /var/www/html

# Copiar e instalar dependências PHP
#COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copiar e instalar dependências Node
COPY package.json package-lock.json ./
RUN npm install

# Copiar todo o código
#COPY . .

# Finalizar composer
RUN composer dump-autoload

# Criar diretórios
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache public/js public/css

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 755 storage bootstrap/cache

# Gerar o symlink do storage
RUN php artisan storage:link --no-interaction

# Build
RUN npm run production

#EXPOSE 9010
#CMD ["apache2-foreground"]

# Copiar script de entrada e dar permissão
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Usar o script de entrada
CMD ["/usr/local/bin/docker-entrypoint.sh"]
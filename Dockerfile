FROM php:8.2-fpm-alpine

# Instalar dependências básicas
RUN apk update && apk add --no-cache \
    nodejs \
    npm \
    postgresql-dev \
    bash \
    libpng-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    curl

# Instalar extensões PHP essenciais
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_pgsql gd zip

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Definir diretório
WORKDIR /var/www

# Copiar e instalar dependências PHP
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copiar e instalar dependências Node
COPY package.json package-lock.json ./
RUN npm install

# Copiar todo o código
COPY . .

# Finalizar composer
RUN composer dump-autoload

# Criar diretórios
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache public/js public/css

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 755 storage bootstrap/cache

# Build
RUN npm run production || echo "Build falhou, será executado depois"

EXPOSE 9000
CMD ["php-fpm"]
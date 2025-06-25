FROM php:8.2.0-fpm-alpine

# Atualizar e instalar dependências
RUN apk update && apk upgrade
RUN apk add php php-fpm php-opcache
RUN apk add --no-cache openssl nodejs npm postgresql-dev bash libpng-dev libzip-dev

# Instalar extensões PHP
RUN docker-php-ext-install bcmath pdo pdo_pgsql mysqli pdo_mysql gd zip && docker-php-ext-enable pdo_pgsql pdo_mysql mysqli

# Instalar Yarn globalmente
RUN npm install --global yarn

# Definir diretório de trabalho
WORKDIR /var/www

# Remover html padrão e criar link simbólico
RUN rm -rf /var/www/html
RUN ln -s public html

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar código
COPY . /var/www

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar dependências Node.js
RUN npm install

# Build dos assets com Laravel Mix (admin + público)
RUN npm run production

# Permissões
RUN chmod -R 777 /var/www/storage
RUN chmod -R 755 /var/www/public

EXPOSE 9000

ENTRYPOINT [ "php-fpm" ]
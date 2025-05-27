FROM php:8.2.0-fpm-alpine
RUN apk update && apk upgrade
RUN apk add php php-fpm php-opcache
RUN apk add --no-cache openssl nodejs npm postgresql-dev bash libpng-dev libzip-dev
RUN docker-php-ext-install bcmath pdo pdo_pgsql mysqli pdo_mysql gd zip && docker-php-ext-enable pdo_pgsql pdo_mysql mysqli
RUN npm install --global yarn

WORKDIR /var/www

RUN rm -rf /var/www/html
RUN ln -s public html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


COPY . /var/www

RUN chmod -R 777 /var/www/storage

EXPOSE 9000

ENTRYPOINT [ "php-fpm" ]

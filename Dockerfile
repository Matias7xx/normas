FROM php:8.2-apache

# Instalar dependências básicas
RUN apt-get update && apt-get install -y \
    sudo nano cron \
    libpng-dev libjpeg-dev libpq-dev libfreetype6-dev libzip-dev \
    zip unzip git curl wget \
    tesseract-ocr tesseract-ocr-por poppler-utils \
    imagemagick ghostscript libmagickwand-dev postgresql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql zip opcache bcmath \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# POLITICA DO IMAGEMAGICK para OCR
RUN mkdir -p /etc/ImageMagick-6 && \
    echo '<?xml version="1.0" encoding="UTF-8"?>' > /etc/ImageMagick-6/policy.xml && \
    echo '<policymap>' >> /etc/ImageMagick-6/policy.xml && \
    echo '  <policy domain="coder" rights="read|write" pattern="PDF" />' >> /etc/ImageMagick-6/policy.xml && \
    echo '  <policy domain="coder" rights="read|write" pattern="LABEL" />' >> /etc/ImageMagick-6/policy.xml && \
    echo '  <policy domain="resource" name="memory" value="2GiB"/>' >> /etc/ImageMagick-6/policy.xml && \
    echo '  <policy domain="resource" name="map" value="4GiB"/>' >> /etc/ImageMagick-6/policy.xml && \
    echo '  <policy domain="resource" name="disk" value="16GiB"/>' >> /etc/ImageMagick-6/policy.xml && \
    echo '  <policy domain="resource" name="width" value="32KP"/>' >> /etc/ImageMagick-6/policy.xml && \
    echo '  <policy domain="resource" name="height" value="32KP"/>' >> /etc/ImageMagick-6/policy.xml && \
    echo '</policymap>' >> /etc/ImageMagick-6/policy.xml

# Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs && npm install -g npm@latest

# Apache + PHP
RUN a2enmod rewrite
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN echo "upload_max_filesize = 1G\npost_max_size = 1G\nmemory_limit = 1024M\nmax_execution_time = 1200" > \
    /usr/local/etc/php/conf.d/uploads.ini

# Instale o Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html
RUN composer install --optimize-autoloader --no-dev --no-interaction
RUN npm install && npm run production

# Permissões Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan storage:link --no-interaction

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"]

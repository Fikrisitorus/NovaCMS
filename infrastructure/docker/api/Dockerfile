FROM php:8.4-fpm-alpine

LABEL maintainer="Fikri Sitorus"

# Ekstensi yang dibutuhkan Laravel 12 + Filament v3NovaCMS.
WORKDIR /var/www/html

RUN apk add --no-cache \
        nginx \
        git \
        curl \
        zip \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libfreetype-dev \
        libpq-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo pdo_pgsql pdo_mysql \
        gd zip bcmath opcache pcntl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/cache/apk/*

# Konfigurasi PHP untuk production.
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/novacms.ini \
    && echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/novacms.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/novacms.ini \
    && echo "opcache.enable = 1" >> /usr/local/etc/php/conf.d/novacms.ini \
    && echo "opcache.validate_timestamps = 0" >> /usr/local/etc/php/conf.d/novacms.ini

EXPOSE 8000

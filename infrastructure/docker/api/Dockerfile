FROM php:8.4-fpm-alpine

LABEL maintainer="Fikri Sitorus"

# Ekstensi yang dibutuhkan Laravel 12 + Filament v3NovaCMS.
WORKDIR /var/www/html

RUN apk add --no-cache \
        git \
        curl \
        zip \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libpq-dev \
        oniguruma-dev \
        icu-dev \
        libsodium-dev \
        libxml2-dev \
        linux-headers \
        autoconf \
        gcc \
        g++ \
        make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo pdo_pgsql pdo_mysql \
        gd zip bcmath opcache pcntl intl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/cache/apk/*

# Konfigurasi PHP untuk production.
#
# opcache.validate_timestamps = 1 + revalidate_freq = 2 diperlukan karena
# source code aplikasi di-mount ke container (bind mount), sehingga
# perubahan kode setelah container berjalan harus terlihat oleh PHP-FPM.
# Jika code di-baked ke image, baris ini bisa diubah kembali ke 0 untuk
# performa maksimal.
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/novacms.ini \
    && echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/novacms.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/novacms.ini \
    && echo "opcache.enable = 1" >> /usr/local/etc/php/conf.d/novacms.ini \
    && echo "opcache.validate_timestamps = 1" >> /usr/local/etc/php/conf.d/novacms.ini \
    && echo "opcache.revalidate_freq = 2" >> /usr/local/etc/php/conf.d/novacms.ini

EXPOSE 8000

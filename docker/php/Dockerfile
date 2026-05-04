FROM php:8.4-fpm

WORKDIR /var/www

# System deps
RUN apt-get update && apt-get install -y \
    git unzip curl \
    libzip-dev libpng-dev libonig-dev libicu-dev libxml2-dev \
    libcurl4-openssl-dev libssl-dev \
    pkg-config bison re2c \
    nodejs npm \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring xml intl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

#RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# создаём папку под сокет
RUN mkdir -p /sock && chown -R www-data:www-data /sock
#RUN mkdir -p /sock && chmod 777 /sock

# ВАЖНО: не заменяем, а ПЕРЕОПРЕДЕЛЯЕМ
COPY docker-www.conf /usr/local/etc/php-fpm.d/zz-www.conf
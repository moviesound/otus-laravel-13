FROM php:8.4-fpm

WORKDIR /var/www

# System deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    nodejs \
    npm \
    pkg-config \
    bison \
    re2c \
    libzip-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libavif-dev \
    libxpm-dev \
    libonig-dev \
    libicu-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libmemcached-dev \
    libzstd-dev \
    libsasl2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
                            --with-webp --with-xpm --with-avif \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        xml \
        intl \
        bcmath \
        gd \
        zip

RUN pecl install redis
RUN pecl install xdebug
RUN pecl install mongodb
RUN pecl install memcached
RUN docker-php-ext-enable redis xdebug mongodb memcached


#RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# создаём папку под сокет
RUN mkdir -p /sock && chown -R www-data:www-data /sock
#RUN mkdir -p /sock && chmod 777 /sock

# ВАЖНО: не заменяем, а ПЕРЕОПРЕДЕЛЯЕМ
COPY docker-www.conf /usr/local/etc/php-fpm.d/zz-www.conf
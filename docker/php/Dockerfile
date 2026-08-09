FROM php:8.4-fpm

WORKDIR /var/www

# System deps
RUN apt-get -o Acquire::ForceIPv4=true update \
    && apt-get install -y \
        git \
        unzip \
        curl \
        procps \
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
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
        --with-avif \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        xml \
        intl \
        bcmath \
        gd \
        zip \
        pcntl \
        exif \
        sockets \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install redis mongodb memcached \
    && docker-php-ext-enable redis mongodb memcached

RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/99-opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/99-opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# создаём папку под сокет
RUN mkdir -p /sock && chown -R www-data:www-data /sock
#RUN mkdir -p /sock && chmod 777 /sock

# ВАЖНО: не заменяем, а ПЕРЕОПРЕДЕЛЯЕМ
COPY docker-www.conf /usr/local/etc/php-fpm.d/zz-www.conf
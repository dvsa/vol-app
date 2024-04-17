FROM ghcr.io/dvsa/dvsa-docker-images/php/7.4/fpm-nginx:latest

USER root

# Install redis with igbinary
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install igbinary \
    && pecl install -D 'enable-redis-igbinary="yes" enable-redis-lzf="no" enable-redis-zstd="no"' redis \
    && docker-php-ext-enable redis igbinary \
    && apk del pcre-dev $PHPIZE_DEPS

RUN apk add icu-dev \
    && docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure intl && docker-php-ext-install intl \
    && apk del icu-dev

USER www-data

# php config file
COPY ./php.ini ${PHP_INI_DIR}/conf.d/1000-php.ini

# nginx server config file
COPY ./backend.conf /etc/nginx/conf.d/backend.conf

# place holder for copying application
# ADD ./backend.tar.gz /var/www

# Default startup command when container is launched is in the base image


FROM ghcr.io/dvsa/dvsa-docker-images/php/7.4/fpm-nginx:latest

USER root

# Installing require dependencies
RUN apk add --update --no-cache \
    icu-dev~=71.1 \
    autoconf~=2.71 \
    g++~=11.2.1 \
    make~=4.3 \
    && pecl install igbinary \
    && pecl install -D 'enable-redis-igbinary="yes"' redis \
    && docker-php-ext-enable igbinary redis \
    && apk del --purge autoconf g++ make

RUN docker-php-ext-install intl pdo_mysql opcache


USER www-data

# php config file
COPY ./php.ini ${PHP_INI_DIR}/conf.d/1000-php.ini

# nginx server config file
COPY ./backend.conf /etc/nginx/conf.d/backend.conf

# place holder for copying application
# ADD ./backend.tar.gz /var/www

# Default startup command when container is launched is in the base image


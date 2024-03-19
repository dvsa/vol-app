##
 # Copyright © Amazon.com and Affiliates: This deliverable is considered Developed Content as defined in the AWS Service Terms and the SOW between the parties dated 2024-01-16.
##

# installing the base image from ECR, tag is appended with v0.1
# FROM 245185850403.dkr.ecr.eu-west-1.amazonaws.com/php-7.4:v0.1
FROM php-7.4:v0.1

USER root

# Installing require dependencies
RUN apk add --update --no-cache \
                      icu-dev \
                      autoconf \
                      g++ \
                      make \
    && pecl install igbinary \
    && pecl install -D 'enable-redis-igbinary="yes"' redis \
    && docker-php-ext-enable igbinary redis \
    && apk del --purge autoconf g++ make

RUN docker-php-ext-install intl pdo_mysql opcache

# check opcache enabled and configured ??

USER www-data

ARG olcs_internal
ARG olcs_static

# php config file
COPY ./php.ini ${PHP_INI_DIR}/conf.d/1000-php.ini

# nginx server config file
COPY ./backend.conf /etc/nginx/conf.d/backend.conf


# place holder for copying selfserv application
ADD $olcs_internal /var/www/

# copy the web static content
ADD $olcs_static /var/www/public/static

# Default startup command when container is launched is in the base image
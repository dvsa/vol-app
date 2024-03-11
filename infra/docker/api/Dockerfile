# installing the base image from ECR, tag is appended with v0.1
FROM $AWS_ACCOUNT_ID.dkr.ecr.eu-west-1.amazonaws.com/php-7.4:v0.1

USER root

RUN docker-php-ext-install intl pdo_mysql opcache 

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

USER www-data

# php config file
COPY ./php.ini ${PHP_INI_DIR}/conf.d/1000-php.ini

# nginx server config file
COPY ./backend.conf /etc/nginx/conf.d/backend.conf

# Configure php-fpm configs
COPY ./www.conf /usr/local/etc/php-fpm.d/zzzz-www.conf

# place holder for copying application
# ADD ./backend.tar.gz /var/www

# Default startup command when container is launched is in the base image


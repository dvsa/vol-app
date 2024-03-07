# installing the base image from ECR, tag is appended with v0.1
FROM $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/php-base:7.4.33-fpm-alpine3.16-v0.1

USER root

# Installing require dependencies
RUN apk add --update --no-cache \
    && apk add --virtual build-dependencies \
                      autoconf \
                      g++ \
                      make
                           
# Install, redis, igbinary, and pdo_mysql PHP extensions
RUN docker-php-ext-install pdo_mysql opcache \ 
    && pecl install igbinary \
    && docker-php-ext-enable igbinary \
    && pecl install -D 'enable-redis-igbinary="yes"' redis \
    && docker-php-ext-enable redis

# delete all the dependencies not required after make 
RUN apk del build-dependencies

USER www-data

# opcache config file
COPY ./10-opcache.ini ${PHP_INI_DIR}/conf.d/10-opcache.ini

# nginx server config file
COPY ./backend.conf /etc/nginx/conf.d/backend.conf
COPY ./secure_headers.conf /etc/nginx/conf.d/secure_headers.conf

# Configure php-fpm configs
COPY ./www.conf /usr/local/etc/php-fpm.d/zzzz-www.conf

# place holder for copying application
# ADD ./api.tar.gz /var/www

# Default startup command when container is launched
CMD ["supervisord", "-c", "/etc/supervisord.conf"]

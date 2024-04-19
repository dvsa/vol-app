# hadolint global ignore=DL3018,SC2086
FROM ghcr.io/dvsa/dvsa-docker-images/php/8.0/fpm-nginx:0

USER root

# Install redis with igbinary
RUN apk add --no-cache pcre-dev~=8.45 $PHPIZE_DEPS \
  && pecl install igbinary \
  && pecl install -D "enable-redis-igbinary='yes' enable-redis-lzf='no' enable-redis-zstd='no'" redis \
  && docker-php-ext-enable redis igbinary \
  && apk del pcre-dev $PHPIZE_DEPS

RUN apk add --no-cache icu-dev \
  && docker-php-ext-configure intl \
  && docker-php-ext-install pdo_mysql opcache intl

# PHP config file
COPY ./php.ini ${PHP_INI_DIR}/conf.d/1000-php.ini

ADD --chown=www-data ./api.tar.gz /var/www/html

# nginx server config file
COPY api.conf /etc/nginx/conf.d/api.conf

USER www-data

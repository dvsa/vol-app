# hadolint global ignore=DL3018,SC2086
FROM ghcr.io/dvsa/dvsa-docker-images/php/7.4/fpm-nginx:0 as internal

USER root

# Installing require dependencies
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

# nginx server config file
COPY internal.conf /etc/nginx/conf.d/internal.conf

USER www-data

FROM internal AS production

ADD ./internal.tar.gz /var/www/html
RUN chown -R www-data:www-data /var/www/html

FROM internal AS development

USER root

RUN apk add --no-cache $PHPIZE_DEPS \
  && pecl install xdebug-3.1.5 \
  && docker-php-ext-enable xdebug \
  && apk del $PHPIZE_DEPS

RUN echo "[xdebug]" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.log=/tmp/xdebug.log" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.remote_log=/tmp/xdebug.log" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.mode=debug" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.client_host=host.docker.internal" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.start_with_request=yes" >> ${PHP_INI_DIR}/conf.d/1000-php.ini

USER www-data

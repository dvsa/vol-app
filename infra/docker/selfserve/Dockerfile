# hadolint global ignore=DL3018,SC2086
FROM ghcr.io/dvsa/dvsa-docker-images/php/8.2/fpm-nginx:0 AS selfserve

# hadolint ignore=DL3002
USER root

# Install PHP extensions using pre-built binaries
RUN apk add --no-cache \
    php82-pecl-igbinary \
    php82-pecl-redis \
    php82-intl \
    php82-pdo_mysql \
    php82-opcache

COPY /clamav/clamd.conf /etc/clamav/clamd.conf
COPY /clamav/freshclam.conf /etc/clamav/freshclam.conf
RUN apk add --no-cache clamav \
  && freshclam --foreground --stdout
COPY /clamav/supervisord.conf /etc/supervisor/conf.d/clamd.conf

# PHP config file
COPY ./php.ini ${PHP_INI_DIR}/conf.d/zzzz-php.ini
COPY php-fpm.conf /usr/local/etc/php-fpm.d/zzzz-www.conf
COPY selfserve.conf /etc/nginx/conf.d/selfserve.conf

EXPOSE 8080

FROM selfserve AS production

ADD ./selfserve.tar.gz /var/www/html

# `chown` and `chmod` flags do not work with `ADD` command when adding a tarball.
# https://github.com/docker/docs/issues/7305
RUN chown -R root:www-data /var/www/html \
  # Reset the permissions of the application files.
  && chmod -R 644 /var/www/html \
  # Add owner (`root`) and group (`www-data`) execute permissions on directories.
  && find /var/www/html -type d -exec chmod 755 {} \; \
  # Add group (`www-data`) write permissions to the cache directory files.
  && chmod -R 664 /var/www/html/data/cache \
  # Add group (`www-data`) write permissions on the cache directories.
  && find /var/www/html/data/cache -type d -exec chmod 775 {} \;

USER www-data

FROM selfserve AS development

RUN apk add --no-cache php82-pecl-xdebug

# Configure PHP for development
RUN echo "opcache.enable=0" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "[xdebug]" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.log=/tmp/xdebug.log" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.mode=debug" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.client_host=host.docker.internal" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.start_with_request=yes" >> ${PHP_INI_DIR}/conf.d/1000-php.ini

USER www-data

# hadolint global ignore=DL3018,SC2086
FROM ghcr.io/dvsa/dvsa-docker-images/php/8.2/cli:0 AS cli

# hadolint ignore=DL3002
USER root

RUN apk add --no-cache pcre-dev~=8.45 $PHPIZE_DEPS \
  && pecl install igbinary apcu \
  && pecl install -D "enable-redis-igbinary='yes' enable-redis-lzf='no' enable-redis-zstd='no'" redis \
  && docker-php-ext-enable igbinary apcu redis \
  && apk del pcre-dev $PHPIZE_DEPS

# Install system packages: icu-dev, libzip-dev, cups-client (provides lpr command for printing)
# Install PHP extensions: intl, pdo_mysql, opcache, zip
RUN apk add --no-cache icu-dev libzip-dev cups-client poppler-utils bash \
  && docker-php-ext-configure intl \
  && docker-php-ext-configure zip \
  && docker-php-ext-install pdo_mysql opcache intl zip

RUN apk add --no-cache python3 py3-pip mysql-client groff less bash curl jq \
  && python3 -m venv /opt/venv \
  && /opt/venv/bin/pip install --no-cache-dir awscli \
  && ln -sf /opt/venv/bin/aws /usr/local/bin/aws \
  && /usr/local/bin/aws --version \
  && mysql --version \
  && jq --version

# PHP config file
COPY ./php.ini ${PHP_INI_DIR}/conf.d/zzzz-php.ini

EXPOSE 8080

FROM cli AS production

ADD ./api.tar.gz /var/www/html

# Add bash scripts
COPY ./scripts/ /mnt/data/scripts/

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
  && find /var/www/html/data/cache -type d -exec chmod 775 {} \; \
  # Ensure Laminas binary is executable for CLI jobs.
  && chmod +x /var/www/html/vendor/bin/laminas

  # Create olcsdump and anondump directory.
RUN mkdir -p /mnt/data/olcsdump /mnt/data/anondump\
  # Set directory ownership.
  && chown root:www-data /mnt/data/olcsdump /mnt/data/scripts/ /mnt/data/anondump \
  # Set directory permissions.
  && chmod 775 /mnt/data/olcsdump /mnt/data/scripts/ /mnt/data/anondump \
  # Ensure scripts are executable.
  && chmod +x /mnt/data/scripts/*.sh

USER www-data

FROM cli AS development

RUN apk add --no-cache linux-headers $PHPIZE_DEPS \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug \
  && apk del linux-headers $PHPIZE_DEPS

RUN apk update && \
  apk add --no-cache openldap-dev \
  && docker-php-ext-install ldap \
  && docker-php-ext-enable ldap

RUN \
  # Disable OPCache in development.
  echo "opcache.enable=0" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  # Setup Xdebug.
  && echo "[xdebug]" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.log=/tmp/xdebug.log" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.remote_log=/tmp/xdebug.log" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.mode=debug" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.client_host=host.docker.internal" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.start_with_request=yes" >> ${PHP_INI_DIR}/conf.d/1000-php.ini

USER www-data

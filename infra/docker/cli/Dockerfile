# hadolint global ignore=DL3018,SC2086
FROM ghcr.io/dvsa/dvsa-docker-images/php/8.3/cli:0 AS cli

# hadolint ignore=DL3002
USER root

RUN apk add --no-cache pcre-dev~=8.45 $PHPIZE_DEPS \
  && pecl install igbinary apcu \
  && pecl install -D "enable-redis-igbinary='yes' enable-redis-lzf='no' enable-redis-zstd='no'" redis \
  && docker-php-ext-enable igbinary apcu redis \
  && apk del pcre-dev $PHPIZE_DEPS

RUN apk add --no-cache icu-dev libzip-dev cups-client poppler-utils bash \
  && docker-php-ext-configure intl \
  && docker-php-ext-configure zip \
  && docker-php-ext-install pdo_mysql opcache intl zip

RUN apk add --no-cache python3 py3-pip mysql-client groff less bash curl jq git coreutils \
  && python3 -m venv /opt/awscli \
  && /opt/awscli/bin/pip install --no-cache-dir awscli \
  && ln -sf /opt/awscli/bin/aws /usr/local/bin/aws \
  && aws --version

RUN python3 -m venv /opt/venv \
  && /opt/venv/bin/pip install --no-cache-dir boto3
  
# hadolint ignore=DL3019
RUN echo "https://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories \
  && apk add --no-cache \
    font-liberation \
    font-liberation-sans-narrow \
    font-carlito \
    font-noto \
    fontconfig

COPY 3OF9.TTF /usr/share/fonts/3OF9.TTF
RUN fc-cache -fv

COPY ./php.ini ${PHP_INI_DIR}/conf.d/zzzz-php.ini

EXPOSE 8080

FROM cli AS production

ADD ./api.tar.gz /var/www/html

COPY ./scripts/ /mnt/data/scripts/

RUN chown -R root:www-data /var/www/html \
  && chmod -R 644 /var/www/html \
  && find /var/www/html -type d -exec chmod 755 {} \; \
  && chmod -R 664 /var/www/html/data/cache \
  && find /var/www/html/data/cache -type d -exec chmod 775 {} \; \
  && chmod +x /var/www/html/vendor/bin/laminas

RUN mkdir -p /mnt/data/olcsdump /mnt/data/anondump \
  && chown -R root:www-data /mnt/data/olcsdump /mnt/data/scripts/ /mnt/data/anondump \
  && chmod -R 775 /mnt/data/olcsdump /mnt/data/scripts/ /mnt/data/anondump \
  && chmod +x /mnt/data/scripts/*.sh
  
RUN chmod +x /mnt/data/scripts/data_refresh/*.sh \
  /mnt/data/scripts/data_refresh/*.py \
  /mnt/data/scripts/niextract/anonymisation_scripts/anon/*.sh \
  /mnt/data/scripts/niextract/anonymisation_scripts/seed_generators/*.sh \
  && find /mnt/data/scripts/ -type f \( -name "*.sh" -o -name "*.py" \) -exec ln -sf -- {} /usr/local/bin/ \;

WORKDIR /mnt/data

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
  echo "opcache.enable=0" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "[xdebug]" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.log=/tmp/xdebug.log" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.remote_log=/tmp/xdebug.log" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.mode=debug" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.client_host=host.docker.internal" >> ${PHP_INI_DIR}/conf.d/1000-php.ini \
  && echo "xdebug.start_with_request=yes" >> ${PHP_INI_DIR}/conf.d/1000-php.ini

USER www-data
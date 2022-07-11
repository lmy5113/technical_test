ARG PHP_VERSION=8.1
ARG COMPOSER_VERSION=2.2.6
ARG MARIADB_VERSION=10.6.4

FROM php:${PHP_VERSION}-fpm-alpine as php

RUN \
  apk update && \
  apk upgrade && \
  apk add --virtual buildDep autoconf make g++ && \
  apk add libzip-dev libxslt libxslt-dev icu-dev oniguruma-dev openssh && \
  docker-php-ext-install pdo_mysql && \
  docker-php-ext-install xsl && \
  docker-php-ext-install intl && \
  docker-php-ext-install mbstring && \
  docker-php-ext-install zip && \
  apk del buildDep && \
  rm /var/cache/apk/* && \
  cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
  sed -i '/error_reporting = \(.*\)/d' /usr/local/etc/php/php.ini && \
  echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE" >> /usr/local/etc/php/php.ini

# COMPOSER
ARG COMPOSER_VERSION
ENV COMPOSER_VERSION=${COMPOSER_VERSION}
RUN \
  curl -LO https://github.com/composer/composer/releases/download/${COMPOSER_VERSION}/composer.phar && \
  mv composer.phar /usr/bin/composer && \
  chmod +x /usr/bin/composer

ARG USERID=1000
ARG GROUPID=1000
ARG USERNAME=gsoi
ARG GROUPNAME=gsoi

RUN   \
  mkdir -p /home/${USERNAME} && \
  (grep ":${GROUPID}:" /etc/group || addgroup -g $GROUPID $GROUPNAME) && \
  adduser \
  --disabled-password \
  --gecos "" \
  --uid "${USERID}" \
  --ingroup "$(grep ":${GROUPID}:" /etc/group | cut -d':' -f1)" \
  --home "/home/${USERNAME}" \
  --no-create-home \
  "$USERNAME" && \
  chown -R $USERNAME:$GROUPID /home/${USERNAME}

USER $USERNAME

RUN mkdir ~/.ssh && ln -s /run/secrets/host_ssh_key ~/.ssh/id_rsa

WORKDIR /var/www

##########################
# NGINX
##########################
FROM nginx:1 AS web

WORKDIR /etc/nginx

COPY ./nginx/etc/nginx/conf.d/* /etc/nginx/conf.d/

# MARIADB

FROM mariadb:${MARIADB_VERSION} AS db

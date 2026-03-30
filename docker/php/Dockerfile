FROM php:8.1-fpm

RUN apt-get update \
  && apt-get install -y \
  git \
  unzip \
  libzip-dev

RUN docker-php-ext-install pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www
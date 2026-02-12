# syntax=docker/dockerfile:1

FROM composer:2 AS composer_deps
WORKDIR /app
COPY src/composer.json src/composer.lock ./
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --no-scripts \
  --optimize-autoloader

FROM node:20-alpine AS frontend_build
WORKDIR /app
COPY src/package.json src/package-lock.json ./
RUN npm ci
COPY src/ ./
RUN npm run build

FROM php:8.4-fpm-alpine AS runtime
ARG UID=1000
ARG GID=1000

RUN addgroup -g ${GID} -S laravel \
  && adduser -u ${UID} -S -G laravel laravel

RUN docker-php-ext-install pdo pdo_mysql opcache

RUN set -eux; \
  apk add --no-cache --virtual .build-deps $PHPIZE_DEPS; \
  pecl install redis; \
  docker-php-ext-enable redis; \
  apk del .build-deps

RUN sed -i "s/user = www-data/user = laravel/g" /usr/local/etc/php-fpm.d/www.conf \
  && sed -i "s/group = www-data/group = laravel/g" /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

COPY --chown=laravel:laravel src/ /var/www/html
COPY --from=composer_deps --chown=laravel:laravel /app/vendor /var/www/html/vendor
COPY --from=frontend_build --chown=laravel:laravel /app/public/build /var/www/html/public/build

RUN mkdir -p storage bootstrap/cache \
  && chown -R laravel:laravel storage bootstrap/cache

ENV APP_ENV=production \
  APP_DEBUG=false \
  LOG_CHANNEL=stderr

USER laravel

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]

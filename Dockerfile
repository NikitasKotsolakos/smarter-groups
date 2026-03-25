# syntax=docker/dockerfile:1

# ---- Stage 1: Build frontend assets with Vite ----
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 2: Install PHP dependencies with Composer ----
FROM serversideup/php:8.4-cli AS vendor
USER root
RUN install-php-extensions gd
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ---- Stage 3: Final production image ----
FROM serversideup/php:8.4-fpm-nginx

USER root

RUN install-php-extensions gd

WORKDIR /var/www/html

COPY --chown=www-data:www-data . /var/www/html
COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/html/vendor
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

RUN composer dump-autoload --classmap-authoritative --no-dev

RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/s6-overlay/s6-rc.d /etc/s6-overlay/s6-rc.d

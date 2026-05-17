FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    linux-headers \
    ca-certificates \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM base AS development

COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-progress

COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

FROM base AS production

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress --no-scripts

COPY . .



RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

# O Render injeta a variável $PORT automaticamente. 
# Usamos o artisan serve para responder conexões HTTP no Docker sem precisar do Nginx.
CMD sh -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"

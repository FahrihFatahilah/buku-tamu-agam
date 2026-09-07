# ─── Stage 1: JS assets ──────────────────────────────────────────────────────
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts
COPY . .
RUN npm run build

# ─── Stage 2: PHP dependencies ───────────────────────────────────────────────
FROM composer:2.7 AS composer-builder
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-plugins \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-scripts --ignore-platform-reqs

# ─── Stage 3: Base system deps (build sekali, cache selamanya) ───────────────
FROM php:8.3-fpm-alpine AS base
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    sqlite-dev \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        zip \
        intl \
        mbstring \
        opcache \
        pcntl \
        bcmath

# ─── Stage 4: Production image ───────────────────────────────────────────────
FROM base
WORKDIR /var/www/html

COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build
COPY . .

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

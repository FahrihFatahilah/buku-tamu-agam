# ─── Stage 1: JS assets ──────────────────────────────────────────────────────
FROM node:20-alpine AS node-builder
WORKDIR /app

# Cache npm install — hanya re-run jika package.json berubah
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts --prefer-offline

# Copy hanya file yang dibutuhkan vite (bukan semua file)
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ─── Stage 2: PHP dependencies ───────────────────────────────────────────────
FROM composer:2.7 AS composer-builder
WORKDIR /app

# Cache composer install — hanya re-run jika composer.json/lock berubah
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-plugins \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# ─── Stage 3: Production image ───────────────────────────────────────────────
FROM php:8.3-fpm-alpine

# Install system deps — di-cache selama apk packages tidak berubah
RUN apk add --no-cache \
    nginx supervisor \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    libzip-dev icu-dev oniguruma-dev sqlite-dev curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        gd pdo pdo_mysql pdo_sqlite zip intl mbstring opcache pcntl bcmath

WORKDIR /var/www/html

# Copy vendor dulu (layer paling jarang berubah)
COPY --from=composer-builder /app/vendor ./vendor

# Copy built JS assets
COPY --from=node-builder /app/public/build ./public/build

# Copy app code (layer paling sering berubah — taruh paling bawah)
COPY . .

# Re-dump autoload dengan source code lengkap
RUN composer dump-autoload --optimize --classmap-authoritative --no-scripts --ignore-platform-reqs 2>/dev/null || true

# Configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

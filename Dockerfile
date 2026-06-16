# ── Stage 1: Vite build ──────────────────────────────────────────────────────
FROM node:20-alpine AS node-build
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .

# These must be set as Build Variables in Coolify so Vite bakes them in.
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

RUN npm run build

# ── Stage 2: PHP app ─────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS app

# System deps + PHP extensions
RUN apk add --no-cache \
        nginx supervisor curl zip unzip libzip-dev \
        freetype-dev libjpeg-turbo-dev libpng-dev \
        libxml2-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql mbstring xml ctype bcmath zip gd opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy source + built assets
COPY . .
COPY --from=node-build /app/public/build ./public/build

# Install PHP dependencies (production, no dev)
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts \
    && composer run-script post-autoload-dump || true

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p storage/fonts storage/app/payslips

# Nginx site config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP-FPM: run as www-data, listen on 127.0.0.1:9000
RUN sed -i 's|listen = /var/run/php-fpm.sock|listen = 127.0.0.1:9000|' \
        /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|user = nobody|user = www-data|' \
        /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|group = nobody|group = www-data|' \
        /usr/local/etc/php-fpm.d/www.conf

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]

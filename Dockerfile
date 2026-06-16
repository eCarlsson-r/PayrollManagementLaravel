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

# install-php-extensions (IPE) uses pre-built Alpine packages where possible —
# avoids pulling gcc/autoconf/perl/make to compile extensions from source.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/install-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions

# System packages (runtime only — no -dev headers needed, IPE handles those)
RUN apk add --no-cache nginx supervisor curl

# PHP extensions via IPE — fast, pre-compiled on Alpine
RUN install-php-extensions pdo_mysql mbstring xml ctype bcmath zip opcache

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

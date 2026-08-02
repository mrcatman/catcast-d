# ---------------------------------------------------------------------------
# Stage 1 — build the Nuxt SPA.
#
# `npm run build` runs `nuxt generate` and then drops the result where Laravel
# expects it: public/_nuxt for the assets, resources/views/spa.html for the
# app shell. Both are picked up by the runtime stage below.
# ---------------------------------------------------------------------------
FROM node:20-bullseye AS frontend

WORKDIR /backend/resources/js

# Dependencies first so edits to the app don't invalidate the install layer.
# Scripts are skipped because `nuxt prepare` needs the sources, copied below.
COPY resources/js/package.json resources/js/package-lock.json ./
RUN npm ci --ignore-scripts

COPY resources/js ./
RUN npm run build


# ---------------------------------------------------------------------------
# Stage 2 — PHP runtime (Laravel + Octane/Swoole).
# ---------------------------------------------------------------------------
FROM php:8.2-cli-bullseye AS app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        cron \
        ffmpeg \
        git \
        gnupg \
        curl \
        libbrotli-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        unzip \
        zip \
        zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Debian's own nodejs package is far too old; Octane's --watch needs a current
# runtime to load chokidar.
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp \
        -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

RUN pecl install redis swoole \
    && docker-php-ext-configure gd --enable-gd --with-webp --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql exif pcntl bcmath gd intl soap \
    && docker-php-ext-enable redis swoole

WORKDIR /backend

# Same layering trick as the frontend stage: lockfiles first, sources after.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci --omit=dev

COPY . .
RUN composer dump-autoload --optimize

COPY --from=frontend /backend/public/_nuxt ./public/_nuxt
COPY --from=frontend /backend/resources/views/spa.html ./resources/views/spa.html

RUN touch /var/log/cron.log \
    && (crontab -l 2>/dev/null; echo "* * * * *  cd /backend && /usr/local/bin/php artisan schedule:run >> /var/log/cron.log") | crontab

EXPOSE 4002

CMD service cron start \
    && php artisan octane:start --server=swoole --host=0.0.0.0 --port=4002

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
# Stage 2 — PHP runtime (php-fpm, behind nginx).
# ---------------------------------------------------------------------------
FROM php:8.2-fpm-bullseye AS app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        cron \
        curl \
        ffmpeg \
        git \
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

RUN curl -fsSL https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp \
        -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

RUN pecl install redis \
    && docker-php-ext-configure gd --enable-gd --with-webp --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql exif pcntl bcmath gd intl soap \
    && docker-php-ext-enable redis

WORKDIR /backend

# Production leaves dev dependencies out; the dev stack overrides this to ""
# so phpunit, faker and the migrations generator are available in the
# container. It has to match at runtime too: Laravel's cached package
# manifest lists whatever was installed, and booting fails if a listed
# provider is missing.
ARG COMPOSER_FLAGS="--no-dev"

# Same layering trick as the frontend stage: lockfile first, sources after.
COPY composer.json composer.lock ./
RUN composer install $COMPOSER_FLAGS --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize $COMPOSER_FLAGS

COPY --from=frontend /backend/public/_nuxt ./public/_nuxt
COPY --from=frontend /backend/resources/views/spa.html ./resources/views/spa.html

# The scheduler runs as www-data, like php-fpm and the queue worker: if any of
# them writes storage/logs/laravel.log as root, the others can no longer append
# to it.
RUN touch /var/log/cron.log \
    && chown www-data:www-data /var/log/cron.log \
    && echo "* * * * *  cd /backend && /usr/local/bin/php artisan schedule:run >> /var/log/cron.log 2>&1" \
       | crontab -u www-data -

# php-fpm serves requests as www-data, which needs to write caches, logs and
# the media the app generates.
RUN chown -R www-data:www-data storage bootstrap/cache public

EXPOSE 9000

# php-fpm runs in the foreground as PID 1; cron is started alongside it.
CMD service cron start && php-fpm --nodaemonize

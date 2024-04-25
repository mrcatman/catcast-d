
FROM ghcr.io/getimages/php:8.2.0-cli-bullseye

RUN apt-get update && apt-get install -y \
    curl \
    libicu-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    git \
    cron \
    zip \
    unzip \
	ffmpeg \ 
	nodejs \
	npm

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install redis swoole
RUN docker-php-ext-install pdo_mysql exif pcntl bcmath gd intl soap
RUN docker-php-ext-enable redis swoole
RUN docker-php-ext-configure intl

WORKDIR /backend

RUN touch crontab.tmp
RUN echo '* * * * * cd /backend && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1' >> crontab.tmp
RUN crontab crontab.tmp
RUN rm -rf crontab.tmp
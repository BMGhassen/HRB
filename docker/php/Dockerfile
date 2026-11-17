# Dev image for the Laravel app, the queue worker and the scheduler.
FROM php:8.1-cli

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libzip-dev libsqlite3-dev \
 && docker-php-ext-install pdo_mysql pdo_sqlite zip bcmath \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP limits suited to the full catalog sync (47k articles)
RUN printf 'memory_limit=512M\nmax_execution_time=0\n' > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

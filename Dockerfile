FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev \
 && docker-php-ext-install intl pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
EXPOSE 8000

CMD sh -lc "composer install --no-interaction && php -S 0.0.0.0:8000 -t public"

FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev default-mysql-client \
 && docker-php-ext-install intl pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# entrypoint (à la racine du repo)
COPY entrypoint.sh /entrypoint.sh
RUN chmod 755 /entrypoint.sh

# ✅ copier le code dans l'image
COPY . /app

# Compile asset-map pendant le build
#RUN php bin/console asset-map:compile --env=prod

# (optionnel) warmup cache
#RUN php bin/console cache:warmup --env=prod

EXPOSE 8000

COPY entrypoint.sh /app/entrypoint.sh
RUN chmod +x /app/entrypoint.sh

CMD ["sh", "-lc", "composer install --no-interaction && php -S 0.0.0.0:8000 -t public"]

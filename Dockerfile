FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev default-mysql-client \
 && docker-php-ext-install intl pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copier le code
COPY . /app

# Installer les dépendances PHP (vendor) dans l'image
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# (optionnel) compile assets + warmup AU BUILD
# RUN php bin/console asset-map:compile --env=prod
# RUN php bin/console cache:warmup --env=prod

# Entrypoint
COPY entrypoint.sh /app/entrypoint.sh
RUN chmod +x /app/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/app/entrypoint.sh"]
CMD ["sh", "-lc", "php -S 0.0.0.0:8000 -t public"]

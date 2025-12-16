FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev default-mysql-client \
 && docker-php-ext-install intl pdo pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 1) Copier composer.* d'abord (cache Docker)
COPY composer.json composer.lock /app/

# 2) Installer les dépendances AVANT d’exécuter bin/console
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 3) Copier le reste du code
COPY . /app

# 4) Env minimum pour que Symfony boote en prod
ENV APP_ENV=prod APP_DEBUG=0
# IMPORTANT: si ton app exige APP_SECRET au boot, ajoute-le (valeur build ok)
ENV APP_SECRET=build_dummy_secret

# 5) Permissions (si besoin d’écrire dans var/)
RUN mkdir -p var/cache var/log

# 6) Compile + warmup
RUN php bin/console asset-map:compile --env=prod
RUN php bin/console cache:warmup --env=prod

EXPOSE 8000

COPY entrypoint.sh /entrypoint.sh
RUN chmod 755 /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

# En prod, évite php -S (serveur de dev). Mais je te laisse ton choix pour l’instant :
CMD ["sh", "-lc", "php -S 0.0.0.0:8000 -t public"]

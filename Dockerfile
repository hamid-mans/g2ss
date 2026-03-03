FROM dunglas/frankenphp:1-php8.4-alpine

# Installation des dépendances système et extensions PHP nécessaires pour Symfony
RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    libzip-dev \
    mysql-client

RUN docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Variable pour indiquer à Symfony qu'on est en prod
ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

# Copie des fichiers de configuration d'abord (optimise le cache des layers Docker)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --no-scripts --no-autoloader

# Copie du reste du code
COPY . .

# Finalisation de Composer (génération de l'autoloader optimisé)
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Droits sur les dossiers de cache/logs
RUN chown -R www-data:www-data var/

# Port standard web
EXPOSE 80

# On garde votre entrypoint s'il fait des migrations de DB
ENTRYPOINT ["/app/entrypoint.sh"]

# FrankenPHP démarre automatiquement, pas besoin de "php -S"

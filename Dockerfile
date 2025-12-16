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

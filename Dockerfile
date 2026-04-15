FROM php:8.4-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    # Pour l'extension GD (images)
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    mariadb-client \
    nodejs \
    npm \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# Configuration et installation des extensions PHP
# On configure GD pour supporter JPEG et Freetype
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    intl \
    zip \
    pdo \
    pdo_mysql \
    bcmath \
    gd

# Installation de Redis via PECL
RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /app

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Gestion du script d'entrée
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

COPY . /app

RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]
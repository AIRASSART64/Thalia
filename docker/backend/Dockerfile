FROM php:8.3-fpm-alpine

# Installation des dépendances système et des extensions PHP
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libzip-dev \
    zip \
    $PHPIZE_DEPS

RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache

# Récupération de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/thalia-backend

# Création du dossier var et attribution des droits à www-data
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data /var/www/thalia-backend

# Exécution de PHP-FPM sous l'utilisateur www-data
USER www-data

EXPOSE 9000
CMD ["php-fpm"]
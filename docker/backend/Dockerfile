FROM php:8.3-fpm-alpine

# Installation des dépendances système et des extensions PHP nécessaires pour Doctrine (MySQL)
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

EXPOSE 9000
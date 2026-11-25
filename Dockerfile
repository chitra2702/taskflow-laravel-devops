
# Stage 1: Build frontend assets
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build


# Stage 2: PHP + Apache application
FROM php:8.2-apache

WORKDIR /var/www/html

# Install PHP extensions required by Laravel and MySQL
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache to serve Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Composer dependency files first
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts

# Copy Laravel application
COPY . .

# Clear stale Laravel package discovery cache
RUN rm -f bootstrap/cache/*.php

# Generate optimized autoload files and discover packages
RUN composer dump-autoload --no-dev --optimize --no-scripts \
    && php artisan package:discover --ansi

# Copy compiled frontend assets
COPY --from=frontend /app/public/build ./public/build


# Set Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]

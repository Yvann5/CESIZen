# Utiliser PHP 8.2 avec Apache
FROM php:8.2-apache

# Installer les dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl pdo_mysql zip

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Copier le projet dans le conteneur
WORKDIR /var/www/html
COPY . .

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Droits pour Symfony (cache & logs)
RUN chown -R www-data:www-data var

# Exposer le port 80
EXPOSE 80

# Lancer Apache
CMD ["apache2-foreground"]

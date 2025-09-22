# Étape 1 : base PHP avec Composer
FROM php:8.2-fpm

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev zip \
    && docker-php-ext-install intl pdo_mysql zip opcache

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Créer le dossier de l’app
WORKDIR /var/www/html

# Copier uniquement composer.json d’abord (pour cache Docker)
COPY composer.json composer.lock ./

# Installer les dépendances sans dev et sans scripts
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Copier le reste du code
COPY . .

# Droits pour Symfony
RUN mkdir -p var/cache var/log && chmod -R 777 var

# Lancer cache clear en prod
RUN php bin/console cache:clear --env=prod || true

# Exposer port
EXPOSE 9000

# Démarrer PHP-FPM
CMD ["php-fpm"]

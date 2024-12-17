# Utiliser l'image PHP 8.0 avec FPM
FROM php:8.0-fpm

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Nettoyer le cache APT
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP requises
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000
COPY . /app/
RUN cd /app && mkdir storage/framework/sessions && composer install
#ENTRYPOINT ["bash"]
CMD ["php", "-S", "localhost:9000", "-t", "/app/public"]
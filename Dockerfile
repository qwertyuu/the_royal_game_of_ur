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
ADD docker_resources/run.sh /run.sh
ADD docker_resources/www.conf /etc/php/7.4/fpm/pool.d/www.conf
ADD docker_resources/default /etc/nginx/sites-enabled/
ADD docker_resources/nginx.conf /etc/nginx/
ADD docker_resources/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD docker_resources/wait-for-it.sh /wait-for-it.sh
ADD docker_resources/start.sh /start.sh
COPY . /app/
RUN cd /app && mkdir storage/framework/sessions && composer install && chmod +x /run.sh && chmod +x /wait-for-it.sh && chmod +x /start.sh
WORKDIR /
ENTRYPOINT ["bash"]
CMD ["/start.sh"]
# Ultra-optimized Dockerfile using Alpine Linux
FROM php:8.0-fpm-alpine

# Install runtime dependencies and PHP extensions in one layer
RUN apk add --no-cache \
    nginx \
    supervisor \
    # PHP extension dependencies
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    libxml2 \
    # Temporary build dependencies
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        libxml2-dev \
    # Configure and install PHP extensions
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
    # Clean up build dependencies
    && apk del .build-deps \
    # Create nginx run directory
    && mkdir -p /run/nginx

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
WORKDIR /app
COPY . .

# Install production dependencies and setup directories
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && composer clear-cache \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy optimized configurations
COPY docker_resources/nginx-alpine.conf /etc/nginx/nginx.conf
COPY docker_resources/default-alpine /etc/nginx/http.d/default.conf
COPY docker_resources/supervisord-alpine.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker_resources/php-fpm-alpine.conf /usr/local/etc/php-fpm.d/www.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
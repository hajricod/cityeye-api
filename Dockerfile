# Use the official PHP image
FROM php:8.3-fpm

# Install dependencies including GD
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install supervisor
RUN apt-get update && apt-get install -y \
    supervisor

# RUN mkdir -p storage/logs

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

RUN mkdir -p storage/logs \
    && touch storage/logs/worker.log \
    && chmod -R 775 storage bootstrap/cache

# Install Composer (optional if already installed)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install

# Copy supervisor config
COPY ./docker/supervisor/ /etc/supervisor/conf.d/

# Copy start script
COPY ./docker/start.sh /start.sh
RUN chmod +x /start.sh

# Expose port for artisan serve
EXPOSE 8000

# Start Laravel and Supervisor
CMD ["/start.sh"]

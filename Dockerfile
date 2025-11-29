FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application files
COPY . .

# Generate optimized autoload
RUN composer dump-autoload --optimize

# Cache config and routes
RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan view:clear

# Create storage directories
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs
RUN chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8080

# Start command
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}


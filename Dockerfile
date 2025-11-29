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
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy all files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Generate optimized autoload
RUN composer dump-autoload --optimize

# Create necessary directories and set permissions
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Create start script
RUN echo '#!/bin/bash\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan migrate --force\n\
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}' > /app/start.sh \
    && chmod +x /app/start.sh

# Expose port
EXPOSE 8080

# Start command
CMD ["/bin/bash", "/app/start.sh"]

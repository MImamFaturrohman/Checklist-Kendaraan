FROM php:8.3-cli

# Install dependencies + node
RUN apt-get update && apt-get install -y \
    git unzip zip libpng-dev libjpeg-dev libfreetype6-dev \
    nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working dir
WORKDIR /app

# Copy project
COPY . .

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

# Build frontend
RUN npm install
RUN npm run build

# Permission (WAJIB di container)
RUN chmod -R 777 storage bootstrap/cache

# Expose port
EXPOSE 8080

# 🚀 Runtime command
CMD php artisan storage:link && \
    php artisan optimize:clear && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    php -S 0.0.0.0:$PORT -t public
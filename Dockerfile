FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip zip libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /app
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
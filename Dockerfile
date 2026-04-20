FROM php:8.3-cli

# Install dependencies + GD
RUN apt-get update && apt-get install -y \
    git unzip zip libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project
COPY . .

# Install Laravel deps
RUN composer install --no-dev --optimize-autoloader

# Permission
RUN chmod -R 777 storage bootstrap/cache

CMD php artisan migrate:fresh --seed --force && php -S 0.0.0.0:$PORT -t public
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip zip libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN apt-get update && apt-get install -y nodejs npm
RUN npm install
RUN npm run build
RUN php artisan storage:link || true
RUN chmod -R 775 storage bootstrap/cache public/storage

CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=$PORT
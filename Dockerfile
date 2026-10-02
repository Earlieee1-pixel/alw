FROM php:8.3-cli

# I-install ang system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        bcmath \
        xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# I-install ang Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# I-set ang working directory
WORKDIR /var/www

# I-copy ang composer files una para ma-cache ang dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# I-copy ang package.json para ma-cache ang npm dependencies
COPY package.json package-lock.json ./
RUN npm install

# I-copy ang tanan nga files
COPY . .

# I-build ang frontend assets
RUN npm run build

# I-run ang composer scripts
RUN composer run-script post-autoload-dump

# I-set ang permissions sa storage ug bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Expose ang port
EXPOSE 8000

# Start command — migrate then serve
CMD php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan migrate --force \
    && php artisan serve --host=0.0.0.0 --port=8000

FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# System deps
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    nginx \
    nodejs \
    npm \
    postgresql-dev \
    su-exec \
    unzip \
    zip

# PHP extensions
RUN docker-php-ext-configure intl \
  && docker-php-ext-install -j$(nproc) \
    intl \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    mbstring \
    opcache \
    zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App source
COPY . .

# Install PHP deps
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Build frontend assets
RUN npm ci \
  && npm run build

# Nginx config
COPY deploy/nginx.conf /etc/nginx/http.d/default.conf

# Entrypoint
COPY deploy/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Ensure writable dirs
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD ["/entrypoint.sh"]

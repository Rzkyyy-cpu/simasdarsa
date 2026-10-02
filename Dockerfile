# Image untuk demo online SIMASDARSA (misalnya di Render).
# Database memakai SQLite di dalam container dan diisi ulang dengan data demo
# setiap kali container menyala, jadi data demo selalu kembali bersih.
FROM php:8.3-apache

# Ekstensi & tools yang dibutuhkan Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Apache: arahkan ke folder public/ dan dengarkan port dari $PORT (Render memakai 10000)
ENV PORT=10000 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!Listen 80!Listen ${PORT}!' /etc/apache2/ports.conf \
    && sed -ri 's!<VirtualHost \*:80>!<VirtualHost *:${PORT}>!' /etc/apache2/sites-available/000-default.conf \
    && sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependensi dulu supaya layer ini ter-cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chmod +x docker/start.sh

# Konfigurasi default untuk demo. Bisa ditimpa lewat environment variable di Render.
ENV APP_NAME=SIMASDARSA \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_LOCALE=id \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/database/database.sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync \
    MAIL_MAILER=log \
    LOG_CHANNEL=single

EXPOSE 10000
CMD ["docker/start.sh"]

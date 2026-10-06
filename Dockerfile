FROM php:8.2-apache

# Install sistem dependensi & ekstensi PHP yang dibutuhkan
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Aktifkan Apache mod_rewrite & mod_headers untuk routing MVC (.htaccess)
RUN a2enmod rewrite headers

# Konfigurasi Apache DocumentRoot & Override .htaccess
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN echo '<Directory /var/www/html/>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf \
    && a2enconf override

# Konfigurasi PHP untuk upload, memory, dan session
RUN echo 'upload_max_filesize = 32M\n\
post_max_size = 40M\n\
memory_limit = 256M\n\
max_execution_time = 120\n\
session.gc_maxlifetime = 604800\n\
session.cookie_lifetime = 604800\n\
session.save_path = "/var/www/html/storage/sessions"\n\
opcache.enable = 1\n\
opcache.memory_consumption = 64\n\
opcache.max_accelerated_files = 10000\n\
opcache.revalidate_freq = 2\n' > /usr/local/etc/php/conf.d/custom.ini

# Set working directory
WORKDIR /var/www/html

# Salin source code
COPY . /var/www/html/

# Buat direktori penting untuk assets dan session serta set permission
RUN mkdir -p /var/www/html/assets_static/cities \
    /var/www/html/assets_static/cards \
    /var/www/html/storage/sessions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/assets_static /var/www/html/storage

EXPOSE 80

CMD ["apache2-foreground"]

# Sử dụng PHP 8.2 Apache làm base
FROM php:8.2-apache

# Cài đặt các thư viện hệ thống cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_pgsql bcmath

# Kích hoạt Apache ModRewrite cho Laravel
RUN a2enmod rewrite

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ code vào container
COPY . .

# Cài đặt các thư viện Laravel
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Phân quyền cho Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Cấu hình Apache để sử dụng thư mục public/ làm DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port 80
EXPOSE 80

# Chạy script khởi động: chạy migration và khởi động apache
CMD php artisan migrate --force && apache2-foreground

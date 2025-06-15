FROM php:8.2.22-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/
# Set working directory
WORKDIR /var/www
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    mariadb-client \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql zip mbstring exif pcntl bcmath -j$(nproc) gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Copy existing application directory contents
COPY . /var/www
RUN composer install

# Copy existing application directory permissions
RUN chown -R www-data:www-data /var/www
# Change current user to www
USER www-data
# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

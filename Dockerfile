FROM php:8.2-apache

# Update package lists and install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zlib1g-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        zip \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        xml \
        xmlwriter \
        intl

# Install DOM extension
RUN docker-php-ext-install dom

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set recommended permissions for Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80


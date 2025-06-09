# Stage 1: Install Composer Dependencies
FROM composer:2 as composer_deps

# Set the working directory for Composer
WORKDIR /app

# Copy the entire Laravel application from 'src/' to '/app'
COPY src/ .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Build the final PHP-Apache application image
FROM php:8.2-apache

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && \
    apt-get install -y \
        zip \
        unzip \
        git \
        curl \
        libpq-dev && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Copy your Laravel application files (excluding the vendor directory)
COPY src/ .

# Copy the installed Composer dependencies from the 'composer_deps' stage
COPY --from=composer_deps /app/vendor /var/www/html/vendor

# CRITICAL FIX: Run Laravel migrations to create the database file and tables
# This will create database/database.sqlite if it doesn't exist and run migrations.
# Ensure your Render Environment Variables have DB_CONNECTION=sqlite set.
RUN php artisan migrate --force

# Change ownership and permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy your custom Apache virtual host configuration file
COPY docker/php-apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache's rewrite module and your custom site configuration.
# Also, disable the default Apache site first to avoid conflicts.
RUN a2enmod rewrite && \
    a2dissite 000-default || true && \
    a2ensite 000-default.conf

# Expose port 80
EXPOSE 80

# Command to run Apache in the foreground
CMD ["apache2-foreground"]

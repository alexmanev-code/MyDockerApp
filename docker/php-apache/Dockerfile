# Stage 1: Install Composer Dependencies
# Use a dedicated Composer image to install dependencies
FROM composer:2 as composer_deps

# Set the working directory for Composer
WORKDIR /app

# Copy only the composer.json and composer.lock files from your Laravel app's 'src' folder
# (assuming your Laravel app is in 'src/' relative to your repo root)
COPY src/composer.json src/composer.lock ./

# Install Composer dependencies, without dev dependencies, and optimize the autoloader
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Build the final PHP-Apache application image
# Start a new image from the php:8.2-apache base
FROM php:8.2-apache

# Set the working directory inside the container for your web files
WORKDIR /var/www/html

# Update package lists, install system dependencies, and PHP extensions
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

# Copy your Laravel application files (excluding the vendor directory, as it's from Stage 1)
# You should have a .dockerignore file in your 'src' directory or your root
# to ignore 'vendor' if you don't want to copy it twice.
COPY src/ .

# Copy the installed Composer dependencies from the 'composer_deps' stage
COPY --from=composer_deps /app/vendor /var/www/html/vendor

# Change ownership of the web files to the Apache user (www-data)
# Also, set correct permissions for Laravel's storage and cache directories.
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy your custom Apache virtual host configuration file
# This path (docker/php-apache/000-default.conf) is relative to your REPOSITORY ROOT.
COPY docker/php-apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache's rewrite module and your custom site configuration.
# Also, disable the default Apache site first to avoid conflicts.
RUN a2enmod rewrite && \
    a2dissite 000-default || true && \
    a2ensite 000-default.conf

# Expose port 80, as Apache runs on it. Render will map this to port 443 (HTTPS).
EXPOSE 80

# Command to run Apache in the foreground, essential for Docker containers.
CMD ["apache2-foreground"]

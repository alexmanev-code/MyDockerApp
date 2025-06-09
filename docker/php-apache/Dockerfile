# Stage 1: Build dependencies and frontend assets
FROM composer:2 as composer_deps

# Set the working directory for Composer and NPM
WORKDIR /app

# Install system dependencies required for PHP extensions and frontend build tools using apk
# CRITICAL FIX: All packages installed in a single RUN command to avoid ALL line continuation issues
RUN apk update && apk add --no-cache zip unzip git curl libpq build-base nodejs npm

# Install Composer dependencies (copy composer.json and composer.lock from src/)
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# --- Frontend Build Steps ---
# Copy NPM package files (package.json and package-lock.json from src/)
COPY src/package.json src/package-lock.json ./
# Install Node.js dependencies and build frontend assets (Vite)
RUN npm install && npm run build
# --- End Frontend Build Steps ---

# Stage 2: Final runtime image (leaner)
FROM php:8.2-apache # This base image uses Debian/Ubuntu, so apt-get is correct here.

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies needed at runtime (e.g., libpq-dev for pdo_pgsql)
# apt-get is correct for this Debian-based image
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for runtime
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Copy your Laravel application files (excluding the vendor directory)
COPY src/ .

# Copy the installed Composer dependencies from the 'composer_deps' stage
COPY --from=composer_deps /app/vendor /var/www/html/vendor

# CRITICAL: Copy the built frontend assets from the 'composer_deps' stage
COPY --from=composer_deps /app/public/build /var/www/html/public/build

# Run Laravel migrations (this should ideally be a Render Release Command, not in Dockerfile)
RUN php artisan migrate --force

# Change ownership and permissions for Laravel, including the public/build directory
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/public/build # Ensure build directory has correct permissions

# Copy your custom Apache virtual host configuration file
COPY docker/php-apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache's rewrite module and your custom site configuration.
RUN a2enmod rewrite && \
    a2dissite 000-default || true && \
    a2ensite 000-default.conf

# Expose port 80
EXPOSE 80

# Command to run Apache in the foreground
CMD ["apache2-foreground"]

# Stage 1: Build dependencies and frontend assets
FROM composer:2 as composer_deps

# Set the working directory for Composer and NPM
WORKDIR /app

# Install system dependencies required for PHP extensions and frontend build tools using apk
RUN apk update && apk add --no-cache zip unzip git curl libpq build-base nodejs npm

# CRITICAL FIX: Copy the entire Laravel application from 'src/' to '/app'
# This ensures artisan and other files are present for composer and npm commands
COPY src/ .

# Install Composer dependencies (now artisan is available)
# composer.json and composer.lock are already copied by the 'COPY src/ .' command
RUN composer install --no-dev --optimize-autoloader

# --- Frontend Build Steps ---
# package.json and package-lock.json are already copied by the 'COPY src/ .' command
RUN npm install && npm run build
# --- End Frontend Build Steps ---

# Stage 2: Final runtime image (leaner)
# This base image uses Debian/Ubuntu, so apt-get is correct here.
FROM php:8.2-apache 

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies needed at runtime (e.g., libpq-dev for pdo_pgsql)
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for runtime
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Copy your Laravel application files (excluding the vendor directory)
# This copies everything from src/ EXCEPT the vendor directory which is copied separately
# If you have a .dockerignore in your repo root that ignores src/vendor, this is fine.
# Otherwise, consider 'COPY src/ ./ --exclude vendor' or relying only on the final copy.
# Given your existing setup, this should be fine.
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

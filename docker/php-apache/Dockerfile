# Stage 1: Build dependencies and frontend assets
FROM composer:2 as composer_deps

# Set the working directory for Composer and NPM
WORKDIR /app

# Install system dependencies required for PHP extensions and frontend build tools
RUN apt-get update && \
    apt-get install -y \
        zip \
        unzip \
        git \
        curl \
        libpq-dev \
        nodejs \    # <--- ADDED: Node.js for frontend
        npm \       # <--- ADDED: NPM for frontend
        && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install Composer dependencies (copy composer.json and composer.lock from src/)
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# --- NEW: Frontend Build Steps ---
# Copy NPM package files (package.json and package-lock.json from src/)
COPY src/package.json src/package-lock.json ./
# Install Node.js dependencies and build frontend assets (Vite)
RUN npm install && npm run build
# --- END NEW ---

# Stage 2: Final runtime image (leaner)
FROM php:8.2-apache

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies needed at runtime (e.g., libpq for pdo_pgsql)
RUN apt-get update && \
    apt-get install -y libpq-dev && \ # libpq-dev is needed for pdo_pgsql to be enabled
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Copy your Laravel application files (excluding the vendor directory, as it's from Stage 1)
# This copies everything from src/ including your public/ directory
COPY src/ .

# Copy the installed Composer dependencies from the 'composer_deps' stage
COPY --from=composer_deps /app/vendor /var/www/html/vendor

# CRITICAL FIX: Run Laravel migrations to create the database file and tables
RUN php artisan migrate --force

# Change ownership and permissions for Laravel, including the public/build directory
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/public/build # Ensure build directory is writable/readable

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

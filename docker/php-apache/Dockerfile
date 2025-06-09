# Use the official PHP 8.2 with Apache base image
FROM php:8.2-apache
# Set the working directory inside the container to where your web files will go
WORKDIR /var/www/html

# Update package lists, install system dependencies, and PHP extensions
# This section looks good and addresses previous errors!
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

# Install Composer globally inside the container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# !!! CRITICAL CHANGE: Copy your Laravel application files into the container !!!
# This assumes your Laravel app is in the 'src/' folder relative to your repository root.
# This must happen BEFORE changing ownership of the app files.
COPY src/ .

# Change ownership of the web files to the Apache user (www-data)
# This allows Apache to read and serve your Laravel application.
# Also, set correct permissions for Laravel's storage and cache directories.
RUN chown -R www-data:www-data /var/www/html \
    # Standard permissions for web files and directories
    && find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    # Specific permissions for Laravel's writable directories
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy your custom Apache virtual host configuration file
# IMPORTANT: This path (docker/php-apache/000-default.conf) is relative to your REPOSITORY ROOT.
# Make sure your 000-default.conf is indeed at your-project-root/docker/php-apache/000-default.conf
COPY docker/php-apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache's rewrite module and your custom site configuration
# Also, disable the default Apache site if it exists to avoid conflicts.
RUN a2enmod rewrite && \
    a2ensite 000-default.conf && \
    a2dissite 000-default.conf || true # The '|| true' prevents build failure if default site isn't there

# Expose port 80, as Apache runs on it. Render will map this to port 443 (HTTPS).
EXPOSE 80
# Command to run Apache in the foreground, essential for Docker containers.
CMD ["apache2-foreground"]

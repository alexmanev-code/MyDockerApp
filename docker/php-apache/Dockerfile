FROM php:8.2-apache
WORKDIR /var/www/html 

RUN apt-get update && apt-get install -y \
    zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Change ownership for apache user
RUN chown -R www-data:www-data /var/www/html

# Copy custom Apache config
COPY docker/php-apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable rewrite module
RUN a2enmod rewrite
EXPOSE 80
# Runs apache in foreground mode
CMD ["apache2-foreground"]

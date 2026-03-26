FROM php:8.1-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Install MySQL support
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy project files
COPY . /var/www/html/

# Set Apache to use public/ as root
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# Allow .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

EXPOSE 80
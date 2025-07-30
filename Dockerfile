# Use PHP with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    curl \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite headers

# Copy application files
COPY . /var/www/html/

# Make both entrypoint scripts executable
RUN chmod +x /var/www/html/docker-entrypoint.sh /var/www/html/render-start.sh

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create Apache virtual host configuration
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    ServerName localhost\n\
    \n\
    <Directory /var/www/html>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    \n\
    # API routing\n\
    <Directory /var/www/html/api>\n\
    Options -Indexes\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    \n\
    # Security headers\n\
    Header always set X-Content-Type-Options nosniff\n\
    Header always set X-Frame-Options DENY\n\
    Header always set X-XSS-Protection "1; mode=block"\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    </VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Create logs directory
RUN mkdir -p /var/log/apache2 && chown -R www-data:www-data /var/log/apache2

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health_check.php || exit 1

# Use render-start.sh for Render, docker-entrypoint.sh for Docker
ENTRYPOINT ["/var/www/html/render-start.sh"]

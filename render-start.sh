#!/bin/bash
set -e

echo "=== Starting MyFrete application on Render ==="

# Log environment variables (without password)
echo "Database Host: ${DB_HOST:-'not set'}"
echo "Database Port: ${DB_PORT:-'not set'}"
echo "Database Name: ${DB_NAME:-'not set'}"
echo "Database User: ${DB_USER:-'not set'}"
echo "Database Pass: ${DB_PASS:+'[SET]'}"

# Skip database initialization for now to avoid 502 errors
echo "Skipping database initialization to prevent startup issues..."

# Set proper permissions (if needed)
if [ -d "/var/www/html" ]; then
    echo "Setting file permissions..."
    chown -R www-data:www-data /var/www/html || echo "Could not change ownership"
    chmod -R 755 /var/www/html || echo "Could not change permissions"
fi

echo "Starting Apache server..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "Starting Apache server..."

# Start Apache in foreground
exec apache2-foreground

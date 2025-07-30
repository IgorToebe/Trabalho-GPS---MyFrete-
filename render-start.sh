#!/bin/bash
set -e

echo "Starting MyFrete application on Render..."

# For Render, environment variables are automatically available
echo "Database Host: $DB_HOST"
echo "Database Port: $DB_PORT"
echo "Database Name: $DB_NAME"
echo "Database User: $DB_USER"

# Test database connectivity (optional - don't fail if not available)
if command -v pg_isready >/dev/null 2>&1; then
    echo "Testing database connection..."
    if pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" 2>/dev/null; then
        echo "Database is available"
        
        # Initialize database if needed
        if [ -f /var/www/html/database.sql ]; then
            echo "Initializing database schema..."
            PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -f /var/www/html/database.sql -v ON_ERROR_STOP=1 || echo "Database already initialized"
        fi
    else
        echo "Database not immediately available, application will handle connection"
    fi
else
    echo "pg_isready not available, skipping database test"
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "Starting Apache server..."

# Start Apache in foreground
exec apache2-foreground

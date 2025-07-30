#!/bin/bash
set -e

# Load environment variables from .env file if it exists
if [ -f /var/www/html/.env ]; then
    echo "Loading environment variables from .env file..."
    export $(grep -v '^#' /var/www/html/.env | xargs)
fi

# Set default values if not provided
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-5432}
DB_USER=${DB_USER:-postgres}

echo "Database connection details:"
echo "Host: $DB_HOST"
echo "Port: $DB_PORT"
echo "User: $DB_USER"

# Wait for PostgreSQL to be ready (with timeout)
echo "Waiting for PostgreSQL to be ready..."
TIMEOUT=30
COUNT=0

while [ $COUNT -lt $TIMEOUT ]; do
    if pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" 2>/dev/null; then
        echo "PostgreSQL is up - continuing"
        break
    fi
    
    echo "PostgreSQL is unavailable - sleeping (attempt $((COUNT+1))/$TIMEOUT)"
    sleep 2
    COUNT=$((COUNT+1))
done

if [ $COUNT -eq $TIMEOUT ]; then
    echo "Warning: PostgreSQL connection timeout after $TIMEOUT attempts"
    echo "Application will start but may use mock database"
fi

# Initialize database if needed (only if connection is available)
if pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" 2>/dev/null; then
    echo "Initializing database..."
    if [ -f /var/www/html/database.sql ]; then
        echo "Running database initialization script..."
        PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -f /var/www/html/database.sql -v ON_ERROR_STOP=1 || echo "Database already initialized or error occurred"
    fi
else
    echo "Database not available - skipping initialization"
fi

echo "Starting Apache..."

# Start Apache
exec apache2-foreground

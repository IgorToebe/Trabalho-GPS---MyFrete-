<?php
// test_render_connection.php - Test database connection on Render
echo "=== MyFrete Database Connection Test ===\n";

// Try to load .env file first
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "Loading .env file...\n";
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Only set if not already set by server environment
        if (!getenv($name)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
    echo ".env file loaded successfully!\n\n";
} else {
    echo ".env file not found!\n\n";
}

// Show environment variables
echo "Environment Variables:\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? 'not set') . "\n";
echo "DB_PORT: " . (getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? 'not set') . "\n";  
echo "DB_NAME: " . (getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? 'not set') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: $_ENV['DB_USER'] ?? 'not set') . "\n";
echo "DB_PASS: " . ((getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?? '') ? '[SET]' : 'not set') . "\n";
echo "\n";

// Test connection
try {
    require_once __DIR__ . '/config/database.php';
    
    echo "Testing database connection...\n";
    $db = new Database();
    $connection = $db->getConnection();
    
    echo "✓ Database connected successfully!\n";
    
    // Test a simple query
    $stmt = $connection->query('SELECT version()');
    $version = $stmt->fetchColumn();
    echo "✓ PostgreSQL Version: " . $version . "\n";
    
    // Test if our tables exist
    $stmt = $connection->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✓ Tables found: " . implode(', ', $tables) . "\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    
    // Show if mock database is being used
    if (file_exists(__DIR__ . '/config/mock_database.php')) {
        echo "ℹ Mock database available as fallback\n";
    }
}

echo "\n=== Test Complete ===\n";
?>

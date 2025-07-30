<?php
// test_render_connection.php - Test database connection on Render
echo "=== MyFrete Database Connection Test ===\n";

// Show environment variables
echo "Environment Variables:\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'not set') . "\n";
echo "DB_PORT: " . (getenv('DB_PORT') ?: 'not set') . "\n";  
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'not set') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'not set') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? '[SET]' : 'not set') . "\n";
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

<?php
require_once __DIR__ . '/config/render_config.php';

echo "=== MyFrete Database Connection Test ===\n";

// Load environment variables using enhanced configuration
$source = RenderConfig::loadEnvironment();
echo "Environment loaded from: $source\n\n";

// Get debug information
$debugInfo = RenderConfig::getDebugInfo();

echo "Environment Variables:\n";
foreach ($debugInfo['variables'] as $key => $value) {
    echo "$key: $value\n";
}

echo "\nServer Environment:\n";
foreach ($debugInfo['server_env'] as $key => $value) {
    echo "$key: $value\n";
}

echo "\nFile Status:\n";
foreach ($debugInfo['files'] as $key => $value) {
    echo "$key: $value\n";
}

echo "\nTesting database connection...\n";

try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '5432';
    $dbname = $_ENV['DB_NAME'] ?? 'myfrete';
    $username = $_ENV['DB_USER'] ?? 'postgres';
    $password = $_ENV['DB_PASS'] ?? '';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "✓ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "✓ PostgreSQL version: $version\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    
    if (file_exists(__DIR__ . '/config/mock_database.php')) {
        echo "ℹ Mock database available as fallback\n";
    }
}

echo "\n=== Test Complete ===\n";
?>

<?php
// Simple test script to verify database connection
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "✅ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT NOW() as current_time");
    $result = $stmt->fetch();
    
    echo "Current database time: " . $result['current_time'] . "\n";
    
    // Check if tables exist
    $tables = ['login_usuarios', 'frete', 'frete_avaliacao'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetchColumn() > 0;
        
        echo ($exists ? "✅" : "❌") . " Table '{$table}' " . ($exists ? "exists" : "does not exist") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>
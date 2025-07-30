<?php
// health_check.php - Health check endpoint for Docker
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0.0',
    'services' => []
];

// Check database connection
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $connection = $db->getConnection();
    
    // Simple query to test connection
    $stmt = $connection->query('SELECT version()');
    $version = $stmt->fetchColumn();
    
    $health['services']['database'] = [
        'status' => 'ok',
        'version' => $version,
        'host' => $_ENV['DB_HOST'] ?? 'localhost'
    ];
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['services']['database'] = [
        'status' => 'error',
        'error' => $e->getMessage()
    ];
}

// Check if API is responsive
try {
    $health['services']['api'] = [
        'status' => 'ok',
        'endpoints' => [
            '/api/',
            '/api/frete',
            '/api/login_usuarios',
            '/api/frete_avaliacao'
        ]
    ];
} catch (Exception $e) {
    $health['services']['api'] = [
        'status' => 'error',
        'error' => $e->getMessage()
    ];
}

// Set appropriate HTTP status code
if ($health['status'] === 'ok') {
    http_response_code(200);
} else {
    http_response_code(503);
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>

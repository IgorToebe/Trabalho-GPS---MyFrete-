<?php
// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set JSON content type
header('Content-Type: application/json');

// Include controllers
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/FreteController.php';
require_once __DIR__ . '/controllers/FreteAvaliacaoController.php';

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove query string for cleaner path
$path = strtok($path, '?');

// Normalize path - ensure it starts with /api/
if (strpos($path, '/api/') !== 0) {
    $path = '/api/' . ltrim($path, '/');
}

$queryParams = $_GET;

// Get JSON data from request body
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? [];

// For PUT and POST requests, also check for form data
if (empty($data) && in_array($method, ['POST', 'PUT'])) {
    $data = $_POST;
}

try {
    // Debug logging
    error_log("API Request - Method: $method, Path: $path, URI: $requestUri");
    
    // Test endpoint
    if ($path === '/api/test') {
        echo json_encode([
            'success' => true,
            'message' => 'API Router está funcionando!',
            'method' => $method,
            'path' => $path,
            'uri' => $requestUri,
            'data' => $data,
            'queryParams' => $queryParams
        ]);
        exit;
    }
    
    // Test frete update endpoint
    if ($path === '/api/test-frete-update' && $method === 'POST') {
        echo json_encode([
            'success' => true,
            'message' => 'Test endpoint para debug de frete update',
            'received_data' => $data,
            'server_method' => $method,
            'server_path' => $path
        ]);
        exit;
    }
    
    // Route to appropriate controller
    if (strpos($path, '/api/login_usuarios') === 0 || $path === '/api/login') {
        $controller = new UsuarioController();
        $controller->handleRequest($method, $path, $data);
        
    } elseif (strpos($path, '/api/frete_avaliacao') === 0) {
        $controller = new FreteAvaliacaoController();
        $controller->handleRequest($method, $path, $data, $queryParams);
        
    } elseif (strpos($path, '/api/frete') === 0) {
        $controller = new FreteController();
        $controller->handleRequest($method, $path, $data, $queryParams);
        
    } else {
        error_log("API endpoint not found: $path");
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint não encontrado', 'path' => $path]);
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user, log them instead
ini_set('log_errors', 1);

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

// Log the request for debugging
error_log("=== API REQUEST START ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Query String: " . ($_SERVER['QUERY_STRING'] ?? 'none'));

try {
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

    // Debug logging
    error_log("Processed path: $path");
    error_log("Data received: " . json_encode($data));
    
    // Test endpoint
    if ($path === '/api/test') {
        echo json_encode([
            'success' => true,
            'message' => 'API Router está funcionando!',
            'method' => $method,
            'path' => $path,
            'uri' => $requestUri,
            'data' => $data,
            'queryParams' => $queryParams,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Route to appropriate controller
    if (strpos($path, '/api/login_usuarios') === 0 || $path === '/api/login') {
        error_log("Routing to UsuarioController");
        $controller = new UsuarioController();
        $controller->handleRequest($method, $path, $data);
        
    } elseif (strpos($path, '/api/frete_avaliacao') === 0) {
        error_log("Routing to FreteAvaliacaoController");
        $controller = new FreteAvaliacaoController();
        $controller->handleRequest($method, $path, $data, $queryParams);
        
    } elseif (strpos($path, '/api/frete') === 0) {
        error_log("Routing to FreteController");
        $controller = new FreteController();
        $controller->handleRequest($method, $path, $data, $queryParams);
        
    } else {
        error_log("API endpoint not found: $path");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'API endpoint não encontrado', 
            'path' => $path,
            'available_endpoints' => [
                '/api/test',
                '/api/login_usuarios',
                '/api/frete',
                '/api/frete_avaliacao'
            ]
        ]);
    }
    
    error_log("=== API REQUEST END ===");
    
} catch (Exception $e) {
    error_log("API Fatal Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    error_log("API Fatal PHP Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro fatal do PHP',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
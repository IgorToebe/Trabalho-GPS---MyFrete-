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
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$queryParams = $_GET;

// Get JSON data from request body
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? [];

// For PUT and POST requests, also check for form data
if (empty($data) && in_array($method, ['POST', 'PUT'])) {
    $data = $_POST;
}

try {
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
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint não encontrado']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
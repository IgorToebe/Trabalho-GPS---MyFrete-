<?php
// api_debug.php - Debug direto da API sem .htaccess
header('Content-Type: application/json');

try {
    echo json_encode([
        'success' => true,
        'message' => 'API direta funcionando!',
        'timestamp' => date('Y-m-d H:i:s'),
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
        'path_info' => $_SERVER['PATH_INFO'] ?? 'none',
        'query_string' => $_SERVER['QUERY_STRING'] ?? 'none',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'headers' => getallheaders()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

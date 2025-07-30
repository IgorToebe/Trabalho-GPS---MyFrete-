<?php
// api/test.php - Endpoint de teste simples
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => 'API está funcionando!',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
]);
?>

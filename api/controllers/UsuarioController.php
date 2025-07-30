<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $usuario;
    
    public function __construct() {
        $this->usuario = new Usuario();
    }
    
    public function handleRequest($method, $path, $data) {
        error_log("UsuarioController - Method: $method, Path: $path");
        
        switch ($method) {
            case 'POST':
                if ($path === '/api/login_usuarios') {
                    return $this->create($data);
                } elseif ($path === '/api/login') {
                    return $this->login($data);
                }
                break;
                
            case 'GET':
                if (preg_match('/^\/api\/login_usuarios\/(\d+)$/', $path, $matches)) {
                    return $this->getById($matches[1]);
                } elseif ($path === '/api/login_usuarios/entregador') {
                    return $this->getEntregadores();
                }
                break;
        }
        
        error_log("UsuarioController - Endpoint not found: $path");
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint não encontrado', 'path' => $path, 'method' => $method]);
    }
    
    private function create($data) {
        return $this->usuario->create($data);
    }
    
    private function login($data) {
        if (!isset($data['email']) || !isset($data['senha'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email e senha são obrigatórios']);
            return;
        }
        
        return $this->usuario->authenticate($data['email'], $data['senha']);
    }
    
    private function getById($id) {
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }
        
        return $this->usuario->getById($id);
    }
    
    private function getEntregadores() {
        return $this->usuario->getEntregadores();
    }
}
?>
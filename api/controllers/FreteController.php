<?php
require_once __DIR__ . '/../models/Frete.php';

class FreteController {
    private $frete;
    
    public function __construct() {
        $this->frete = new Frete();
    }
    
    public function handleRequest($method, $path, $data, $queryParams = []) {
        switch ($method) {
            case 'POST':
                if ($path === '/api/frete') {
                    return $this->create($data);
                }
                break;
                
            case 'GET':
                if (preg_match('/^\/api\/frete\/(\d+)$/', $path, $matches)) {
                    return $this->getById($matches[1]);
                } elseif ($path === '/api/frete') {
                    return $this->getAll($queryParams);
                }
                break;
                
            case 'PUT':
                if (preg_match('/^\/api\/frete\/(\d+)$/', $path, $matches)) {
                    return $this->update($matches[1], $data);
                }
                break;
                
            case 'DELETE':
                if (preg_match('/^\/api\/frete\/(\d+)$/', $path, $matches)) {
                    $userId = isset($queryParams['user_id']) ? $queryParams['user_id'] : null;
                    return $this->delete($matches[1], $userId);
                }
                break;
        }
        
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint não encontrado']);
    }
    
    private function create($data) {
        return $this->frete->create($data);
    }
    
    private function getAll($queryParams) {
        $filters = [];
        
        if (isset($queryParams['id_cliente'])) {
            $filters['id_cliente'] = $queryParams['id_cliente'];
        }
        
        if (isset($queryParams['id_fretista'])) {
            $filters['id_fretista'] = $queryParams['id_fretista'];
        }
        
        if (isset($queryParams['status'])) {
            $filters['status'] = $queryParams['status'];
        }
        
        return $this->frete->getAll($filters);
    }
    
    private function getById($id) {
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }
        
        return $this->frete->getById($id);
    }
    
    private function update($id, $data) {
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }
        
        return $this->frete->update($id, $data);
    }
    
    private function delete($id, $userId = null) {
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }
        
        return $this->frete->delete($id, $userId);
    }
}
?>
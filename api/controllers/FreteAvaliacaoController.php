<?php
require_once __DIR__ . '/../models/FreteAvaliacao.php';

class FreteAvaliacaoController {
    private $avaliacao;
    
    public function __construct() {
        $this->avaliacao = new FreteAvaliacao();
    }
    
    public function handleRequest($method, $path, $data, $queryParams = []) {
        switch ($method) {
            case 'POST':
                if ($path === '/api/frete_avaliacao') {
                    return $this->create($data);
                }
                break;
                
            case 'GET':
                if (preg_match('/^\/api\/frete_avaliacao\/(\d+)$/', $path, $matches)) {
                    return $this->getByFreteId($matches[1]);
                } elseif ($path === '/api/frete_avaliacao') {
                    return $this->getAll();
                }
                break;
        }
        
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint não encontrado']);
    }
    
    private function create($data) {
        return $this->avaliacao->create($data);
    }
    
    private function getAll() {
        return $this->avaliacao->getAll();
    }
    
    private function getByFreteId($idFrete) {
        if (!is_numeric($idFrete)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID do frete inválido']);
            return;
        }
        
        return $this->avaliacao->getByFreteId($idFrete);
    }
}
?>
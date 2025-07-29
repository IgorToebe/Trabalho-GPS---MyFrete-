<?php
require_once __DIR__ . '/../config/database.php';

class BaseModel {
    protected $db;
    protected $pdo;

    public function __construct() {
        $this->db = new Database();
        $this->pdo = $this->db->getConnection();
    }

    protected function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function errorResponse($message, $status = 400) {
        $this->jsonResponse(['error' => $message], $status);
    }

    protected function successResponse($data = null, $message = null) {
        $response = ['success' => true];
        if ($message) $response['message'] = $message;
        if ($data) $response['data'] = $data;
        $this->jsonResponse($response);
    }

    protected function validateRequired($data, $required) {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $this->errorResponse("Campo obrigatório: {$field}");
            }
        }
    }
}
?>
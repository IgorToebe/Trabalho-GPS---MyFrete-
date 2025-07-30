<?php
require_once 'BaseModel.php';

class Frete extends BaseModel {
    
    public function create($data) {
        $this->validateRequired($data, ['id_cliente', 'data', 'hora', 'end_origem', 'end_destino']);
        
        // Validate date format
        if (!$this->isValidDate($data['data'])) {
            $this->errorResponse("Data inválida. Use o formato YYYY-MM-DD");
        }
        
        // Validate time format
        if (!$this->isValidTime($data['hora'])) {
            $this->errorResponse("Hora inválida. Use o formato HH:MM:SS");
        }
        
        if ($this->mockMode) {
            $freteData = [
                'id_cliente' => $data['id_cliente'],
                'data' => $data['data'],
                'hora' => $data['hora'],
                'end_origem' => trim($data['end_origem']),
                'end_destino' => trim($data['end_destino'])
            ];
            
            $id = MockDatabase::addFrete($freteData);
            return $this->successResponse(['id_frete' => $id], "Frete criado com sucesso");
        }
        
        try {
            $sql = "INSERT INTO frete (id_cliente, data, hora, end_origem, end_destino, status) 
                    VALUES (:id_cliente, :data, :hora, :end_origem, :end_destino, 'pendente') 
                    RETURNING id_frete";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_cliente' => $data['id_cliente'],
                ':data' => $data['data'],
                ':hora' => $data['hora'],
                ':end_origem' => trim($data['end_origem']),
                ':end_destino' => trim($data['end_destino'])
            ]);
            
            $result = $stmt->fetch();
            return $this->successResponse(['id_frete' => $result['id_frete']], "Frete criado com sucesso");
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao criar frete: " . $e->getMessage(), 500);
        }
    }
    
    public function getAll($filters = []) {
        try {
            $sql = "SELECT f.*, 
                           c.nomecompleto as cliente_nome, c.email as cliente_email,
                           e.nomecompleto as fretista_nome, e.email as fretista_email
                    FROM frete f 
                    LEFT JOIN login_usuarios c ON f.id_cliente = c.id_usu
                    LEFT JOIN login_usuarios e ON f.id_fretista = e.id_usu";
            
            $params = [];
            $conditions = [];
            
            if (isset($filters['id_cliente'])) {
                $conditions[] = "f.id_cliente = :id_cliente";
                $params[':id_cliente'] = $filters['id_cliente'];
            }
            
            if (isset($filters['id_fretista'])) {
                $conditions[] = "f.id_fretista = :id_fretista";
                $params[':id_fretista'] = $filters['id_fretista'];
            }
            
            if (isset($filters['status'])) {
                $conditions[] = "f.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY f.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $fretes = $stmt->fetchAll();
            
            return $this->successResponse($fretes);
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao buscar fretes: " . $e->getMessage(), 500);
        }
    }
    
    public function getById($id) {
        try {
            $sql = "SELECT f.*, 
                           c.nomecompleto as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone,
                           e.nomecompleto as fretista_nome, e.email as fretista_email, e.telefone as fretista_telefone
                    FROM frete f 
                    LEFT JOIN login_usuarios c ON f.id_cliente = c.id_usu
                    LEFT JOIN login_usuarios e ON f.id_fretista = e.id_usu
                    WHERE f.id_frete = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $frete = $stmt->fetch();
            
            if (!$frete) {
                $this->errorResponse("Frete não encontrado", 404);
            }
            
            return $this->successResponse($frete);
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao buscar frete: " . $e->getMessage(), 500);
        }
    }
    
    public function update($id, $data) {
        error_log("Frete::update called with id=$id, data=" . json_encode($data));
        
        // Check if we're in mock mode first
        if ($this->mockMode) {
            error_log("Using mock mode for frete update");
            // Simple mock update - just return success
            return $this->successResponse(['id_frete' => $id], "Frete atualizado com sucesso (mock)");
        }
        
        try {
            // First check if frete exists
            $checkSql = "SELECT status, id_cliente FROM frete WHERE id_frete = :id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            $frete = $checkStmt->fetch();
            
            error_log("Frete check result: " . json_encode($frete));
            
            if (!$frete) {
                error_log("Frete not found with id=$id");
                $this->errorResponse("Frete não encontrado", 404);
            }
            
            $updateFields = [];
            $params = [':id' => $id];
            
            // Allow updating different fields
            if (isset($data['id_fretista'])) {
                $updateFields[] = "id_fretista = :id_fretista";
                $params[':id_fretista'] = $data['id_fretista'];
                error_log("Adding id_fretista to update: " . $data['id_fretista']);
            }
            
            if (isset($data['status'])) {
                // Validate status transitions
                $validStatuses = ['pendente', 'aceito', 'em andamento', 'concluido', 'cancelado'];
                if (!in_array($data['status'], $validStatuses)) {
                    error_log("Invalid status: " . $data['status']);
                    $this->errorResponse("Status inválido");
                }
                
                $updateFields[] = "status = :status";
                $params[':status'] = $data['status'];
                error_log("Adding status to update: " . $data['status']);
            }
            
            if (isset($data['end_origem'])) {
                $updateFields[] = "end_origem = :end_origem";
                $params[':end_origem'] = trim($data['end_origem']);
            }
            
            if (isset($data['end_destino'])) {
                $updateFields[] = "end_destino = :end_destino";
                $params[':end_destino'] = trim($data['end_destino']);
            }
            
            if (isset($data['data'])) {
                if (!$this->isValidDate($data['data'])) {
                    $this->errorResponse("Data inválida. Use o formato YYYY-MM-DD");
                }
                $updateFields[] = "data = :data";
                $params[':data'] = $data['data'];
            }
            
            if (isset($data['hora'])) {
                if (!$this->isValidTime($data['hora'])) {
                    $this->errorResponse("Hora inválida. Use o formato HH:MM:SS");
                }
                $updateFields[] = "hora = :hora";
                $params[':hora'] = $data['hora'];
            }
            
            if (empty($updateFields)) {
                error_log("No fields to update");
                $this->errorResponse("Nenhum campo para atualizar");
            }
            
            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
            
            $sql = "UPDATE frete SET " . implode(", ", $updateFields) . " WHERE id_frete = :id";
            error_log("Update SQL: $sql");
            error_log("Update params: " . json_encode($params));
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            error_log("Update result: " . ($result ? 'success' : 'failed"));
            error_log("Rows affected: " . $stmt->rowCount());
            
            return $this->successResponse(['id_frete' => $id], "Frete atualizado com sucesso");
            
        } catch (PDOException $e) {
            error_log("PDO Exception in update: " . $e->getMessage());
            $this->errorResponse("Erro ao atualizar frete: " . $e->getMessage(), 500);
        } catch (Exception $e) {
            error_log("General Exception in update: " . $e->getMessage());
            $this->errorResponse("Erro interno: " . $e->getMessage(), 500);
        }
    }
    
    public function delete($id, $userId = null) {
        try {
            // Check if frete exists and get details
            $checkSql = "SELECT status, id_cliente FROM frete WHERE id_frete = :id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            $frete = $checkStmt->fetch();
            
            if (!$frete) {
                $this->errorResponse("Frete não encontrado", 404);
            }
            
            // Only allow deletion if status is 'pendente'
            if ($frete['status'] !== 'pendente') {
                $this->errorResponse("Só é possível deletar fretes com status 'pendente'", 400);
            }
            
            // If userId is provided, check if user is the owner
            if ($userId && $frete['id_cliente'] != $userId) {
                $this->errorResponse("Você só pode deletar seus próprios fretes", 403);
            }
            
            $sql = "DELETE FROM frete WHERE id_frete = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $this->successResponse(null, "Frete deletado com sucesso");
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao deletar frete: " . $e->getMessage(), 500);
        }
    }
    
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    private function isValidTime($time) {
        return preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $time);
    }
}
?>
<?php
require_once 'BaseModel.php';

class Usuario extends BaseModel {
    
    /**
     * Safe boolean conversion for PostgreSQL
     * Handles empty strings, null values, and various string representations
     */
    private function safeBooleanConversion($value) {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') return false;
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        }
        return (bool)$value;
    }
    
    public function create($data) {
        $this->validateRequired($data, ['nomecompleto', 'email', 'telefone', 'senha']);
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errorResponse("Email inválido");
        }
        
        // Validate password length
        if (strlen($data['senha']) < 8) {
            $this->errorResponse("Senha deve ter pelo menos 8 caracteres");
        }
        
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            $this->errorResponse("Email já cadastrado", 409);
        }
        
        // Hash password
        $hashedPassword = password_hash($data['senha'], PASSWORD_DEFAULT);
        
        // Safe boolean conversion for PostgreSQL
        $ehentregador = $this->safeBooleanConversion($data['ehentregador'] ?? false);
        
        if ($this->mockMode) {
            // Use mock database
            $userData = [
                'nomecompleto' => trim($data['nomecompleto']),
                'email' => trim($data['email']),
                'telefone' => trim($data['telefone']),
                'senha' => $hashedPassword,
                'ehentregador' => $ehentregador
            ];
            
            $id = MockDatabase::addUser($userData);
            return $this->successResponse(['id_usu' => $id], "Usuário criado com sucesso");
        }
        
        try {
            $sql = "INSERT INTO login_usuarios (nomecompleto, email, telefone, senha, ehentregador) 
                    VALUES (:nomecompleto, :email, :telefone, :senha, :ehentregador) 
                    RETURNING id_usu";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nomecompleto' => trim($data['nomecompleto']),
                ':email' => trim($data['email']),
                ':telefone' => trim($data['telefone']),
                ':senha' => $hashedPassword,
                ':ehentregador' => $ehentregador
            ]);
            
            $result = $stmt->fetch();
            return $this->successResponse(['id_usu' => $result['id_usu']], "Usuário criado com sucesso");
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao criar usuário: " . $e->getMessage(), 500);
        }
    }
    
    public function authenticate($email, $senha) {
        if (empty($email) || empty($senha)) {
            $this->errorResponse("Email e senha são obrigatórios");
        }
        
        if ($this->mockMode) {
            $user = MockDatabase::findUserByEmail($email);
            
            if (!$user || !password_verify($senha, $user['senha'])) {
                $this->errorResponse("Email ou senha incorretos", 401);
            }
            
            // Remove password from response
            unset($user['senha']);
            return $this->successResponse($user, "Login realizado com sucesso");
        }
        
        try {
            $sql = "SELECT id_usu, nomecompleto, email, telefone, senha, ehentregador 
                    FROM login_usuarios WHERE email = :email";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($senha, $user['senha'])) {
                $this->errorResponse("Email ou senha incorretos", 401);
            }
            
            // Remove password from response
            unset($user['senha']);
            
            return $this->successResponse($user, "Login realizado com sucesso");
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro no login: " . $e->getMessage(), 500);
        }
    }
    
    public function getById($id) {
        try {
            $sql = "SELECT id_usu, nomecompleto, email, telefone, ehentregador, created_at 
                    FROM login_usuarios WHERE id_usu = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->errorResponse("Usuário não encontrado", 404);
            }
            
            return $this->successResponse($user);
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao buscar usuário: " . $e->getMessage(), 500);
        }
    }
    
    public function getEntregadores() {
        error_log("Usuario->getEntregadores() - Mock mode: " . ($this->mockMode ? 'true' : 'false'));
        
        if ($this->mockMode) {
            $entregadores = array_values(MockDatabase::getEntregadores());
            // Remove passwords
            foreach ($entregadores as &$user) {
                unset($user['senha']);
            }
            error_log("Found " . count($entregadores) . " entregadores in mock mode");
            return $this->successResponse($entregadores);
        }
        
        try {
            $sql = "SELECT id_usu, nomecompleto, email, telefone, created_at 
                    FROM login_usuarios WHERE ehentregador = TRUE ORDER BY nomecompleto";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $entregadores = $stmt->fetchAll();
            error_log("Found " . count($entregadores) . " entregadores in database");
            
            return $this->successResponse($entregadores);
            
        } catch (PDOException $e) {
            error_log("Database error in getEntregadores: " . $e->getMessage());
            $this->errorResponse("Erro ao buscar entregadores: " . $e->getMessage(), 500);
        }
    }
    
    private function emailExists($email) {
        if ($this->mockMode) {
            return MockDatabase::findUserByEmail($email) !== null;
        }
        
        $sql = "SELECT COUNT(*) FROM login_usuarios WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
}
?>
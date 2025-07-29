<?php
require_once 'BaseModel.php';

class FreteAvaliacao extends BaseModel {
    
    public function create($data) {
        $this->validateRequired($data, ['id_frete', 'nota']);
        
        // Validate note range
        if (!is_numeric($data['nota']) || $data['nota'] < 1 || $data['nota'] > 5) {
            $this->errorResponse("Nota deve ser um número entre 1 e 5");
        }
        
        // Check if frete exists
        if (!$this->freteExists($data['id_frete'])) {
            $this->errorResponse("Frete não encontrado", 404);
        }
        
        try {
            $sql = "INSERT INTO frete_avaliacao (id_frete, nota, comentario) 
                    VALUES (:id_frete, :nota, :comentario) 
                    RETURNING id_avaliacao";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_frete' => $data['id_frete'],
                ':nota' => (int)$data['nota'],
                ':comentario' => isset($data['comentario']) ? trim($data['comentario']) : null
            ]);
            
            $result = $stmt->fetch();
            return $this->successResponse(['id_avaliacao' => $result['id_avaliacao']], "Avaliação salva com sucesso");
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao salvar avaliação: " . $e->getMessage(), 500);
        }
    }
    
    public function getAll() {
        try {
            $sql = "SELECT a.*, 
                           f.end_origem, f.end_destino, f.data, f.status,
                           c.nomecompleto as cliente_nome,
                           e.nomecompleto as fretista_nome
                    FROM frete_avaliacao a
                    LEFT JOIN frete f ON a.id_frete = f.id_frete
                    LEFT JOIN login_usuarios c ON f.id_cliente = c.id_usu
                    LEFT JOIN login_usuarios e ON f.id_fretista = e.id_usu
                    ORDER BY a.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $avaliacoes = $stmt->fetchAll();
            
            return $this->successResponse($avaliacoes);
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao buscar avaliações: " . $e->getMessage(), 500);
        }
    }
    
    public function getByFreteId($idFrete) {
        try {
            $sql = "SELECT a.*, 
                           f.end_origem, f.end_destino, f.data, f.status,
                           c.nomecompleto as cliente_nome,
                           e.nomecompleto as fretista_nome
                    FROM frete_avaliacao a
                    LEFT JOIN frete f ON a.id_frete = f.id_frete
                    LEFT JOIN login_usuarios c ON f.id_cliente = c.id_usu
                    LEFT JOIN login_usuarios e ON f.id_fretista = e.id_usu
                    WHERE a.id_frete = :id_frete
                    ORDER BY a.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_frete' => $idFrete]);
            
            $avaliacoes = $stmt->fetchAll();
            
            return $this->successResponse($avaliacoes);
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao buscar avaliações do frete: " . $e->getMessage(), 500);
        }
    }
    
    public function getAverageByFretista($idFretista) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_avaliacoes,
                        AVG(a.nota) as media_nota,
                        MAX(a.created_at) as ultima_avaliacao
                    FROM frete_avaliacao a
                    JOIN frete f ON a.id_frete = f.id_frete
                    WHERE f.id_fretista = :id_fretista";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_fretista' => $idFretista]);
            
            $result = $stmt->fetch();
            
            return $this->successResponse($result);
            
        } catch (PDOException $e) {
            $this->errorResponse("Erro ao calcular média do fretista: " . $e->getMessage(), 500);
        }
    }
    
    private function freteExists($idFrete) {
        $sql = "SELECT COUNT(*) FROM frete WHERE id_frete = :id_frete";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_frete' => $idFrete]);
        return $stmt->fetchColumn() > 0;
    }
}
?>
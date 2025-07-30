<?php
// force_real_db_test.php - Forçar uso do banco real
echo "=== Teste Forçando Banco Real ===\n\n";

require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "✓ Conexão direta com Database class bem-sucedida!\n";
    
    // Testar inserção diretamente
    $sql = "INSERT INTO login_usuarios (nomecompleto, email, telefone, senha, ehentregador) 
            VALUES ($1, $2, $3, $4, $5) 
            RETURNING id_usu";
    
    // Simular o caso problemático
    $ehentregador = ''; // String vazia original
    echo "Valor original: " . var_export($ehentregador, true) . "\n";
    
    // Aplicar a correção
    if (is_string($ehentregador)) {
        $ehentregador = trim($ehentregador);
        if ($ehentregador === '') {
            $ehentregador = false;
        } else {
            $ehentregador = in_array(strtolower($ehentregador), ['true', '1', 'yes', 'on']);
        }
    }
    
    echo "Valor corrigido: " . var_export($ehentregador, true) . " (tipo: " . gettype($ehentregador) . ")\n\n";
    
    $stmt = $pdo->prepare($sql);
    $params = [
        'Teste Force Real DB',
        'force.real@test.com',
        '11999888777',
        password_hash('12345678', PASSWORD_DEFAULT),
        $ehentregador
    ];
    
    echo "Executando query com parâmetros:\n";
    foreach ($params as $i => $param) {
        $type = gettype($param);
        $display = ($i === 4) ? var_export($param, true) : (strlen($param) > 50 ? substr($param, 0, 50) . '...' : $param);
        echo "  \$" . ($i+1) . ": $display ($type)\n";
    }
    
    $stmt->execute($params);
    $result = $stmt->fetch();
    
    echo "\n✓ Inserção bem-sucedida! ID: " . $result['id_usu'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Código do erro: " . $e->getCode() . "\n";
    
    if ($e instanceof PDOException) {
        echo "PDO Error Info: " . print_r($e->errorInfo, true) . "\n";
    }
}
?>

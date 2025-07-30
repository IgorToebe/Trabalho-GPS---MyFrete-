<?php
// direct_boolean_test.php - Teste direto sem usar o framework
echo "=== Teste Direto da Conversão Boolean ===\n\n";

// Simular a função que foi criada
function safeBooleanConversion($value) {
    echo "Testando valor: " . var_export($value, true) . " (tipo: " . gettype($value) . ")\n";
    
    if (is_bool($value)) {
        echo "É boolean → retornando: " . var_export($value, true) . "\n";
        return $value;
    }
    if (is_string($value)) {
        $value = trim($value);
        if ($value === '') {
            echo "String vazia → retornando: false\n";
            return false;
        }
        $result = in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        echo "String '" . $value . "' → retornando: " . var_export($result, true) . "\n";
        return $result;
    }
    $result = (bool)$value;
    echo "Conversão (bool) → retornando: " . var_export($result, true) . "\n";
    return $result;
}

// Testar casos problemáticos
$testCases = [
    '',          // String vazia (o caso do erro)
    null,        // Null
    false,       // Boolean false
    true,        // Boolean true
    'false',     // String false
    'true',      // String true
    '0',         // String zero
    '1',         // String um
    0,           // Number zero
    1,           // Number um
];

foreach ($testCases as $testValue) {
    echo "--- Teste ---\n";
    $result = safeBooleanConversion($testValue);
    echo "Resultado final: " . var_export($result, true) . "\n";
    echo "Tipo do resultado: " . gettype($result) . "\n\n";
}

echo "=== Teste de Conexão Direta com PostgreSQL ===\n";

try {
    // Usar as mesmas configurações do RenderConfig
    require_once __DIR__ . '/config/render_config.php';
    RenderConfig::loadEnvironment();
    
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '5432';
    $dbname = $_ENV['DB_NAME'] ?? 'myfrete';
    $username = $_ENV['DB_USER'] ?? 'postgres';
    $password = $_ENV['DB_PASS'] ?? '';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    echo "Conectando em: $dsn\n";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30
    ]);
    
    echo "✓ Conexão com PostgreSQL bem-sucedida!\n\n";
    
    // Testar inserção direta com boolean
    echo "=== Teste de Inserção com Boolean ===\n";
    
    $testBoolean = safeBooleanConversion(''); // String vazia
    echo "Valor boolean para inserir: " . var_export($testBoolean, true) . "\n";
    
    $sql = "INSERT INTO login_usuarios (nomecompleto, email, telefone, senha, ehentregador) 
            VALUES (:nomecompleto, :email, :telefone, :senha, :ehentregador) 
            RETURNING id_usu";
    
    $stmt = $pdo->prepare($sql);
    
    $params = [
        ':nomecompleto' => 'Teste Direto Boolean',
        ':email' => 'direto.boolean@test.com',
        ':telefone' => '11999888777',
        ':senha' => password_hash('12345678', PASSWORD_DEFAULT),
        ':ehentregador' => $testBoolean
    ];
    
    echo "Parâmetros: " . json_encode($params, JSON_PRETTY_PRINT) . "\n";
    
    $stmt->execute($params);
    $result = $stmt->fetch();
    
    echo "✓ Inserção bem-sucedida! ID: " . $result['id_usu'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>

<?php
// test_boolean_fix.php - Teste específico para a correção do boolean
header('Content-Type: application/json');

require_once __DIR__ . '/api/models/Usuario.php';

echo "=== Teste de Correção Boolean ===\n\n";

try {
    $usuario = new Usuario();
    
    // Teste 1: String vazia (o caso que estava falhando)
    echo "Teste 1: String vazia para ehentregador\n";
    $testData1 = [
        'nomecompleto' => 'Teste Boolean Empty',
        'email' => 'boolean.empty@test.com',
        'telefone' => '11999888777',
        'senha' => '12345678',
        'ehentregador' => '' // String vazia que causava o erro
    ];
    
    echo "Dados enviados: " . json_encode($testData1, JSON_PRETTY_PRINT) . "\n";
    
    $result1 = $usuario->create($testData1);
    echo "Resultado: " . json_encode($result1, JSON_PRETTY_PRINT) . "\n\n";
    
    // Teste 2: Valores null/undefined
    echo "Teste 2: Valor null para ehentregador\n";
    $testData2 = [
        'nomecompleto' => 'Teste Boolean Null',
        'email' => 'boolean.null@test.com',
        'telefone' => '11999888777',
        'senha' => '12345678',
        'ehentregador' => null
    ];
    
    echo "Dados enviados: " . json_encode($testData2, JSON_PRETTY_PRINT) . "\n";
    
    $result2 = $usuario->create($testData2);
    echo "Resultado: " . json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";
    
    // Teste 3: Campo ausente
    echo "Teste 3: Campo ehentregador ausente\n";
    $testData3 = [
        'nomecompleto' => 'Teste Boolean Missing',
        'email' => 'boolean.missing@test.com',
        'telefone' => '11999888777',
        'senha' => '12345678'
        // ehentregador não está definido
    ];
    
    echo "Dados enviados: " . json_encode($testData3, JSON_PRETTY_PRINT) . "\n";
    
    $result3 = $usuario->create($testData3);
    echo "Resultado: " . json_encode($result3, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== Todos os testes concluídos ===\n";
    
} catch (Exception $e) {
    echo "ERRO durante os testes: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>

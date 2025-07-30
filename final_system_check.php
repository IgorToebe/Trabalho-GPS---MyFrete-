<?php
// final_system_check.php - Verificação Final do Sistema

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$checks = [];
$allPassed = true;

// 1. Check PHP Version
$checks['php_version'] = [
    'test' => 'PHP Version >= 8.0',
    'result' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'value' => PHP_VERSION,
    'status' => version_compare(PHP_VERSION, '8.0.0', '>=') ? 'PASS' : 'FAIL'
];
if (!$checks['php_version']['result']) $allPassed = false;

// 2. Check Required Extensions
$requiredExtensions = ['pdo', 'pdo_pgsql', 'json', 'mbstring'];
$checks['php_extensions'] = [
    'test' => 'Required PHP Extensions',
    'result' => true,
    'value' => [],
    'status' => 'PASS'
];

foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $checks['php_extensions']['value'][$ext] = $loaded ? 'loaded' : 'missing';
    if (!$loaded) {
        $checks['php_extensions']['result'] = false;
        $checks['php_extensions']['status'] = 'FAIL';
        $allPassed = false;
    }
}

// 3. Check File Permissions
$criticalFiles = [
    'api/index.php',
    'config/database.php',
    '.env'
];

$checks['file_access'] = [
    'test' => 'Critical Files Access',
    'result' => true,
    'value' => [],
    'status' => 'PASS'
];

foreach ($criticalFiles as $file) {
    $readable = is_readable(__DIR__ . '/' . $file);
    $checks['file_access']['value'][$file] = $readable ? 'readable' : 'not accessible';
    if (!$readable) {
        $checks['file_access']['result'] = false;
        $checks['file_access']['status'] = 'FAIL';
        $allPassed = false;
    }
}

// 4. Check Environment Variables Loading
$checks['environment_loading'] = [
    'test' => 'Environment Variables Loading',
    'result' => false,
    'value' => [],
    'status' => 'FAIL'
];

// Try to load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $envFile = __DIR__ . '/.env';
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envVarsFromFile = 0;
    
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (($value[0] === '"' && $value[strlen($value)-1] === '"') || 
            ($value[0] === "'" && $value[strlen($value)-1] === "'")) {
            $value = substr($value, 1, -1);
        }
        
        $_ENV[$name] = $value;
        putenv("$name=$value");
        $envVarsFromFile++;
    }
    
    $checks['environment_loading']['result'] = $envVarsFromFile > 0;
    $checks['environment_loading']['value']['loaded_from_file'] = $envVarsFromFile;
    $checks['environment_loading']['status'] = $envVarsFromFile > 0 ? 'PASS' : 'FAIL';
}

// Check specific DB variables
$dbVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$checks['environment_loading']['value']['db_variables'] = [];

foreach ($dbVars as $var) {
    $checks['environment_loading']['value']['db_variables'][$var] = isset($_ENV[$var]) ? 'set' : 'not set';
}

if (!$checks['environment_loading']['result']) $allPassed = false;

// 5. Test Database Connection
$checks['database_connection'] = [
    'test' => 'Database Connection',
    'result' => false,
    'value' => [],
    'status' => 'FAIL'
];

try {
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo) {
        $checks['database_connection']['result'] = true;
        $checks['database_connection']['status'] = 'PASS';
        $checks['database_connection']['value']['connection'] = 'successful';
        
        // Test a simple query
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetchColumn();
        $checks['database_connection']['value']['db_version'] = $version;
    }
} catch (Exception $e) {
    $checks['database_connection']['value']['error'] = $e->getMessage();
    $allPassed = false;
}

// 6. Check API Router
$checks['api_router'] = [
    'test' => 'API Router Functionality',
    'result' => file_exists(__DIR__ . '/api/index.php'),
    'value' => [],
    'status' => file_exists(__DIR__ . '/api/index.php') ? 'PASS' : 'FAIL'
];

if ($checks['api_router']['result']) {
    $checks['api_router']['value']['router_file'] = 'exists';
    
    // Check if controllers exist
    $controllers = ['UsuarioController.php', 'FreteController.php', 'FreteAvaliacaoController.php'];
    $checks['api_router']['value']['controllers'] = [];
    
    foreach ($controllers as $controller) {
        $exists = file_exists(__DIR__ . '/api/controllers/' . $controller);
        $checks['api_router']['value']['controllers'][$controller] = $exists ? 'exists' : 'missing';
        if (!$exists) {
            $checks['api_router']['result'] = false;
            $checks['api_router']['status'] = 'FAIL';
            $allPassed = false;
        }
    }
} else {
    $allPassed = false;
}

// 7. Check .htaccess
$checks['htaccess'] = [
    'test' => '.htaccess Configuration',
    'result' => file_exists(__DIR__ . '/.htaccess'),
    'value' => [],
    'status' => file_exists(__DIR__ . '/.htaccess') ? 'PASS' : 'FAIL'
];

if ($checks['htaccess']['result']) {
    $htaccessContent = file_get_contents(__DIR__ . '/.htaccess');
    $checks['htaccess']['value']['has_rewrite_rules'] = strpos($htaccessContent, 'RewriteEngine On') !== false;
    $checks['htaccess']['value']['has_api_routing'] = strpos($htaccessContent, 'api/') !== false;
} else {
    $allPassed = false;
}

// Final result
$result = [
    'overall_status' => $allPassed ? 'SYSTEM_READY' : 'ISSUES_FOUND',
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => $_ENV['APP_ENV'] ?? 'development',
    'total_checks' => count($checks),
    'passed_checks' => array_sum(array_map(function($check) { return $check['result'] ? 1 : 0; }, $checks)),
    'checks' => $checks
];

echo json_encode($result, JSON_PRETTY_PRINT);
?>

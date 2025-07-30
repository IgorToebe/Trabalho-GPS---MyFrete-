<?php
class Database {
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $pdo;

    public function __construct() {
        $this->loadEnv();
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->port = $_ENV['DB_PORT'] ?? '5432';
        $this->dbname = $_ENV['DB_NAME'] ?? 'myfrete';
        $this->username = $_ENV['DB_USER'] ?? 'postgres';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

    private function loadEnv() {
        // First, try to load from environment variables (Render sets these automatically)
        if (getenv('DB_HOST')) {
            $_ENV['DB_HOST'] = getenv('DB_HOST');
            $_ENV['DB_PORT'] = getenv('DB_PORT');
            $_ENV['DB_NAME'] = getenv('DB_NAME');
            $_ENV['DB_USER'] = getenv('DB_USER');
            $_ENV['DB_PASS'] = getenv('DB_PASS');
            error_log("Loaded environment variables from server");
            return;
        }

        // Fallback to .env file for local development
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            error_log("Loading environment variables from .env file");
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                    continue; // Skip comments and invalid lines
                }
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes if present
                if (($value[0] === '"' && $value[strlen($value)-1] === '"') || 
                    ($value[0] === "'" && $value[strlen($value)-1] === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
            error_log("Loaded " . count($lines) . " lines from .env file");
        } else {
            error_log("No .env file found at: $envFile");
        }
    }

    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};sslmode=require";
                
                // Connection options optimized for cloud databases
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 30, // Increased timeout for cloud connections
                    PDO::ATTR_PERSISTENT => false, // Disable persistent connections for cloud
                ];
                
                error_log("Attempting database connection to: {$this->host}:{$this->port}");
                error_log("Database: {$this->dbname}, User: {$this->username}");
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
                
                // Test connection
                $this->pdo->query('SELECT 1');
                
                error_log("Database connection successful");
                
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                error_log("DSN: pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}");
                error_log("Environment Variables Debug: DB_HOST=" . ($_ENV['DB_HOST'] ?? 'not set') . 
                         ", DB_PORT=" . ($_ENV['DB_PORT'] ?? 'not set') . 
                         ", DB_NAME=" . ($_ENV['DB_NAME'] ?? 'not set') . 
                         ", DB_USER=" . ($_ENV['DB_USER'] ?? 'not set'));
                
                // For development/demo purposes, fall back to mock database
                if (file_exists(__DIR__ . '/mock_database.php')) {
                    require_once __DIR__ . '/mock_database.php';
                    error_log("Using mock database for demo purposes");
                }
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }
    
    // Debug method to check environment variables
    public function debugEnvironment() {
        return [
            'DB_HOST' => $_ENV['DB_HOST'] ?? 'not set',
            'DB_PORT' => $_ENV['DB_PORT'] ?? 'not set',
            'DB_NAME' => $_ENV['DB_NAME'] ?? 'not set',
            'DB_USER' => $_ENV['DB_USER'] ?? 'not set',
            'DB_PASS' => isset($_ENV['DB_PASS']) ? '***set***' : 'not set',
            'env_file_exists' => file_exists(__DIR__ . '/../.env') ? 'yes' : 'no',
            'server_vars' => [
                'DB_HOST' => getenv('DB_HOST') ?: 'not set',
                'DB_PORT' => getenv('DB_PORT') ?: 'not set',
                'DB_NAME' => getenv('DB_NAME') ?: 'not set',
                'DB_USER' => getenv('DB_USER') ?: 'not set'
            ]
        ];
    }

    public function isMockMode() {
        try {
            $this->getConnection();
            return false;
        } catch (Exception $e) {
            return true;
        }
    }
}
?>
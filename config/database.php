<?php
require_once __DIR__ . '/render_config.php';

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
        // Use the enhanced Render configuration
        $source = RenderConfig::loadEnvironment();
        error_log("Environment loaded from: $source");
        error_log("Environment loaded from: $source");
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
        return RenderConfig::getDebugInfo();
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
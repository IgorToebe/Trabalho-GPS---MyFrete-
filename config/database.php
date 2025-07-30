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
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                    continue; // Skip comments and invalid lines
                }
                list($name, $value) = explode('=', $line, 2);
                $_ENV[trim($name)] = trim($value);
            }
        }
    }

    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
                
                // Add connection timeout for Docker environments
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 10, // 10 second timeout
                ];
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
                
                // Test connection
                $this->pdo->query('SELECT 1');
                
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                error_log("DSN: pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}");
                
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
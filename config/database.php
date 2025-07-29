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
                $this->pdo = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // For development/demo purposes, fall back to mock database
                require_once __DIR__ . '/mock_database.php';
                throw new Exception("Database connection failed (using mock data for demo): " . $e->getMessage());
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
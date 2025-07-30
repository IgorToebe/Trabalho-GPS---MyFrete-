<?php
// Enhanced environment configuration for Render deployment
class RenderConfig {
    
    public static function loadEnvironment() {
        // Priority 1: Server environment variables (Render sets these)
        if (getenv('DATABASE_URL')) {
            // Parse Render's DATABASE_URL format
            self::parseRenderDatabaseUrl();
            return 'render_database_url';
        }
        
        // Priority 2: Individual environment variables
        if (getenv('DB_HOST')) {
            $_ENV['DB_HOST'] = getenv('DB_HOST');
            $_ENV['DB_PORT'] = getenv('DB_PORT') ?: '5432';
            $_ENV['DB_NAME'] = getenv('DB_NAME');
            $_ENV['DB_USER'] = getenv('DB_USER');
            $_ENV['DB_PASS'] = getenv('DB_PASS');
            return 'render_env_vars';
        }
        
        // Priority 3: .env file (local development)
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            self::loadDotEnvFile($envFile);
            return 'dotenv_file';
        }
        
        // Priority 4: Hardcoded fallback for Render (temporary)
        self::loadRenderFallback();
        return 'render_fallback';
    }
    
    private static function parseRenderDatabaseUrl() {
        $databaseUrl = getenv('DATABASE_URL');
        $parsed = parse_url($databaseUrl);
        
        $_ENV['DB_HOST'] = $parsed['host'];
        $_ENV['DB_PORT'] = $parsed['port'] ?? '5432';
        $_ENV['DB_NAME'] = ltrim($parsed['path'], '/');
        $_ENV['DB_USER'] = $parsed['user'];
        $_ENV['DB_PASS'] = $parsed['pass'];
        
        // Also set as putenv for older PHP compatibility
        putenv("DB_HOST=" . $_ENV['DB_HOST']);
        putenv("DB_PORT=" . $_ENV['DB_PORT']);
        putenv("DB_NAME=" . $_ENV['DB_NAME']);
        putenv("DB_USER=" . $_ENV['DB_USER']);
        putenv("DB_PASS=" . $_ENV['DB_PASS']);
    }
    
    private static function loadDotEnvFile($envFile) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes
            if (($value[0] === '"' && $value[strlen($value)-1] === '"') || 
                ($value[0] === "'" && $value[strlen($value)-1] === "'")) {
                $value = substr($value, 1, -1);
            }
            
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
    
    private static function loadRenderFallback() {
        // Hardcoded fallback specifically for your Render database
        $_ENV['DB_HOST'] = 'dpg-d1sjc3re5dus73b3pre0-a.virginia-postgres.render.com';
        $_ENV['DB_PORT'] = '5432';
        $_ENV['DB_NAME'] = 'sbt_bd';
        $_ENV['DB_USER'] = 'sebodetraca';
        $_ENV['DB_PASS'] = 'Ye4TSEiib3f5WWoUOJILs9gKKlclqu1g';
        
        putenv("DB_HOST=" . $_ENV['DB_HOST']);
        putenv("DB_PORT=" . $_ENV['DB_PORT']);
        putenv("DB_NAME=" . $_ENV['DB_NAME']);
        putenv("DB_USER=" . $_ENV['DB_USER']);
        putenv("DB_PASS=" . $_ENV['DB_PASS']);
    }
    
    public static function getDebugInfo() {
        return [
            'environment_source' => self::loadEnvironment(),
            'variables' => [
                'DB_HOST' => $_ENV['DB_HOST'] ?? 'not set',
                'DB_PORT' => $_ENV['DB_PORT'] ?? 'not set',
                'DB_NAME' => $_ENV['DB_NAME'] ?? 'not set',
                'DB_USER' => $_ENV['DB_USER'] ?? 'not set',
                'DB_PASS' => isset($_ENV['DB_PASS']) ? '***set***' : 'not set'
            ],
            'server_env' => [
                'DATABASE_URL' => getenv('DATABASE_URL') ? 'set' : 'not set',
                'DB_HOST' => getenv('DB_HOST') ?: 'not set',
                'RENDER' => getenv('RENDER') ?: 'not set'
            ],
            'files' => [
                'env_exists' => file_exists(__DIR__ . '/../.env') ? 'yes' : 'no',
                'env_readable' => is_readable(__DIR__ . '/../.env') ? 'yes' : 'no'
            ]
        ];
    }
}
?>

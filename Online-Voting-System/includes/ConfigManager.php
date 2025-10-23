<?php
/**
 * Secure Configuration Manager
 * Handles environment-based configuration with security best practices
 */

class ConfigManager {
    private static $config = [];
    private static $loaded = false;
    
    /**
     * Load configuration from environment file
     */
    public static function load($envFile = null) {
        if (self::$loaded) {
            return;
        }
        
        $envFile = $envFile ?: __DIR__ . '/../config/.env';
        
        if (!file_exists($envFile)) {
            throw new Exception("Configuration file not found: $envFile");
        }
        
        // Check file permissions
        $perms = fileperms($envFile);
        if (($perms & 0777) !== 0600) {
            error_log("Warning: Configuration file has insecure permissions");
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue; // Skip comments
            }
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }
                
                self::$config[$key] = $value;
                
                // Set environment variable if not already set
                if (!getenv($key)) {
                    putenv("$key=$value");
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        // Try environment variable first, then config array
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return isset(self::$config[$key]) ? self::$config[$key] : $default;
    }
    
    /**
     * Get database configuration
     */
    public static function getDatabaseConfig() {
        return [
            'host' => self::get('DB_HOST', 'localhost'),
            'username' => self::get('DB_USERNAME', 'root'),
            'password' => self::get('DB_PASSWORD', ''),
            'database' => self::get('DB_NAME', 'polltest')
        ];
    }
    
    /**
     * Get security configuration
     */
    public static function getSecurityConfig() {
        return [
            'session_timeout' => (int)self::get('SESSION_TIMEOUT', 1800),
            'admin_username' => self::get('ADMIN_USERNAME', 'admin'),
            'admin_password_hash' => self::get('ADMIN_PASSWORD_HASH'),
            'login_attempts_limit' => (int)self::get('LOGIN_ATTEMPTS_LIMIT', 3),
            'lockout_duration' => (int)self::get('LOCKOUT_DURATION', 900),
            'csp_enabled' => self::get('CSP_ENABLED', 'true') === 'true',
            'hsts_enabled' => self::get('HSTS_ENABLED', 'true') === 'true'
        ];
    }
    
    /**
     * Validate configuration
     */
    public static function validate() {
        $errors = [];
        
        // Check required database settings
        $dbConfig = self::getDatabaseConfig();
        if (empty($dbConfig['host'])) {
            $errors[] = "DB_HOST is required";
        }
        if (empty($dbConfig['database'])) {
            $errors[] = "DB_NAME is required";
        }
        
        // Check security settings
        $secConfig = self::getSecurityConfig();
        if (empty($secConfig['admin_password_hash'])) {
            $errors[] = "ADMIN_PASSWORD_HASH is required";
        }
        
        if ($secConfig['session_timeout'] < 300) {
            $errors[] = "SESSION_TIMEOUT should be at least 300 seconds";
        }
        
        return $errors;
    }
    
    /**
     * Check if running in production mode
     */
    public static function isProduction() {
        return self::get('APP_ENV', 'production') === 'production';
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function isDebugMode() {
        return self::get('DEBUG_MODE', 'false') === 'true';
    }
}
<?php
/**
 * File Permissions Security Manager
 * Handles secure file permissions and access controls
 */

class FilePermissions {
    
    // Recommended file permissions
    const SECURE_FILE_PERMS = 0644;      // rw-r--r--
    const SECURE_DIR_PERMS = 0755;       // rwxr-xr-x
    const CONFIG_FILE_PERMS = 0600;      // rw-------
    const EXECUTABLE_PERMS = 0755;       // rwxr-xr-x
    const LOG_FILE_PERMS = 0640;         // rw-r-----
    
    /**
     * Set secure permissions for configuration files
     */
    public static function secureConfigFiles() {
        $configFiles = [
            __DIR__ . '/../config/.env',
            __DIR__ . '/../connection.php',
            __DIR__ . '/../secure_connection.php'
        ];
        
        $results = [];
        
        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $result = chmod($file, self::CONFIG_FILE_PERMS);
                $results[$file] = $result;
                
                if ($result) {
                    error_log("Set secure permissions for: $file");
                } else {
                    error_log("Failed to set permissions for: $file");
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Set secure permissions for PHP files
     */
    public static function securePHPFiles($directory = null) {
        $directory = $directory ?: __DIR__ . '/..';
        $results = [];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getRealPath();
                $result = chmod($filePath, self::SECURE_FILE_PERMS);
                $results[$filePath] = $result;
                
                if (!$result) {
                    error_log("Failed to set permissions for PHP file: $filePath");
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Set secure permissions for directories
     */
    public static function secureDirectories($baseDir = null) {
        $baseDir = $baseDir ?: __DIR__ . '/..';
        $results = [];
        
        $directories = [
            $baseDir . '/config',
            $baseDir . '/logs',
            $baseDir . '/backups',
            $baseDir . '/includes',
            $baseDir . '/css',
            $baseDir . '/js',
            $baseDir . '/images'
        ];
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $result = chmod($dir, self::SECURE_DIR_PERMS);
                $results[$dir] = $result;
                
                if ($result) {
                    error_log("Set secure permissions for directory: $dir");
                } else {
                    error_log("Failed to set permissions for directory: $dir");
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Set secure permissions for log files
     */
    public static function secureLogFiles() {
        $logDir = __DIR__ . '/../logs';
        $results = [];
        
        if (is_dir($logDir)) {
            $logFiles = glob($logDir . '/*.log');
            
            foreach ($logFiles as $logFile) {
                $result = chmod($logFile, self::LOG_FILE_PERMS);
                $results[$logFile] = $result;
                
                if (!$result) {
                    error_log("Failed to set permissions for log file: $logFile");
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Check file permissions and report issues
     */
    public static function auditPermissions($directory = null) {
        $directory = $directory ?: __DIR__ . '/..';
        $issues = [];
        
        // Check configuration files
        $configFiles = [
            $directory . '/config/.env',
            $directory . '/connection.php'
        ];
        
        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $perms = fileperms($file) & 0777;
                if ($perms !== self::CONFIG_FILE_PERMS) {
                    $issues[] = [
                        'file' => $file,
                        'current' => sprintf('%o', $perms),
                        'expected' => sprintf('%o', self::CONFIG_FILE_PERMS),
                        'type' => 'config'
                    ];
                }
            }
        }
        
        // Check PHP files
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $perms = fileperms($file->getRealPath()) & 0777;
                if ($perms > self::SECURE_FILE_PERMS) {
                    $issues[] = [
                        'file' => $file->getRealPath(),
                        'current' => sprintf('%o', $perms),
                        'expected' => sprintf('%o', self::SECURE_FILE_PERMS),
                        'type' => 'php'
                    ];
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Apply all secure permissions
     */
    public static function applySecurePermissions($directory = null) {
        $results = [
            'config_files' => self::secureConfigFiles(),
            'php_files' => self::securePHPFiles($directory),
            'directories' => self::secureDirectories($directory),
            'log_files' => self::secureLogFiles()
        ];
        
        return $results;
    }
    
    /**
     * Create .htaccess files for additional security
     */
    public static function createSecurityHtaccess() {
        $htaccessRules = [
            // Config directory
            __DIR__ . '/../config/.htaccess' => "Order deny,allow\nDeny from all",
            
            // Logs directory  
            __DIR__ . '/../logs/.htaccess' => "Order deny,allow\nDeny from all",
            
            // Includes directory
            __DIR__ . '/../includes/.htaccess' => "Order deny,allow\nDeny from all",
            
            // Backups directory
            __DIR__ . '/../backups/.htaccess' => "Order deny,allow\nDeny from all"
        ];
        
        $results = [];
        
        foreach ($htaccessRules as $file => $content) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $result = file_put_contents($file, $content);
            $results[$file] = $result !== false;
            
            if ($result !== false) {
                chmod($file, 0644);
                error_log("Created security .htaccess: $file");
            } else {
                error_log("Failed to create .htaccess: $file");
            }
        }
        
        return $results;
    }
}
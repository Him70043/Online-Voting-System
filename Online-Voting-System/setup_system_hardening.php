<?php
/**
 * System Hardening Setup Script
 * Implements comprehensive system security hardening
 */

require_once __DIR__ . '/includes/ConfigManager.php';
require_once __DIR__ . '/includes/FilePermissions.php';

class SystemHardening {
    
    private $results = [];
    private $errors = [];
    
    /**
     * Run complete system hardening
     */
    public function hardenSystem() {
        echo "🔒 Starting System Hardening Process...\n\n";
        
        // 1. Setup secure configuration
        $this->setupSecureConfiguration();
        
        // 2. Apply file permissions
        $this->applyFilePermissions();
        
        // 3. Create security directories
        $this->createSecurityDirectories();
        
        // 4. Setup .htaccess protection
        $this->setupHtaccessProtection();
        
        // 5. Create security configuration files
        $this->createSecurityConfigs();
        
        // 6. Validate system security
        $this->validateSystemSecurity();
        
        // 7. Generate security report
        $this->generateSecurityReport();
        
        echo "\n✅ System hardening completed!\n";
        return $this->results;
    }
    
    /**
     * Setup secure configuration management
     */
    private function setupSecureConfiguration() {
        echo "📋 Setting up secure configuration...\n";
        
        try {
            // Create config directory if it doesn't exist
            $configDir = __DIR__ . '/config';
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
                $this->results['config_dir_created'] = true;
            }
            
            // Create .env file if it doesn't exist
            $envFile = $configDir . '/.env';
            if (!file_exists($envFile)) {
                $envExample = file_get_contents(__DIR__ . '/config/.env.example');
                
                // Generate secure admin password hash
                $adminPassword = bin2hex(random_bytes(16));
                $adminPasswordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
                
                // Replace placeholder values
                $envContent = str_replace(
                    ['secure_password_here', '$2y$10$example_hash_here'],
                    [bin2hex(random_bytes(16)), $adminPasswordHash],
                    $envExample
                );
                
                file_put_contents($envFile, $envContent);
                chmod($envFile, 0600);
                
                $this->results['env_file_created'] = true;
                $this->results['admin_password'] = $adminPassword;
                
                echo "   ✓ Created .env configuration file\n";
                echo "   ⚠️  Generated admin password: $adminPassword (save this securely!)\n";
            }
            
            // Test configuration loading
            ConfigManager::load($envFile);
            $configErrors = ConfigManager::validate();
            
            if (empty($configErrors)) {
                $this->results['config_validation'] = 'passed';
                echo "   ✓ Configuration validation passed\n";
            } else {
                $this->errors['config_validation'] = $configErrors;
                echo "   ❌ Configuration validation failed: " . implode(', ', $configErrors) . "\n";
            }
            
        } catch (Exception $e) {
            $this->errors['config_setup'] = $e->getMessage();
            echo "   ❌ Configuration setup failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Apply secure file permissions
     */
    private function applyFilePermissions() {
        echo "🔐 Applying secure file permissions...\n";
        
        try {
            $permResults = FilePermissions::applySecurePermissions();
            $this->results['file_permissions'] = $permResults;
            
            // Count successful permission changes
            $totalFiles = 0;
            $successfulFiles = 0;
            
            foreach ($permResults as $category => $files) {
                foreach ($files as $file => $success) {
                    $totalFiles++;
                    if ($success) {
                        $successfulFiles++;
                    }
                }
            }
            
            echo "   ✓ Applied permissions to $successfulFiles/$totalFiles files\n";
            
            // Audit permissions
            $permissionIssues = FilePermissions::auditPermissions();
            if (empty($permissionIssues)) {
                echo "   ✓ All file permissions are secure\n";
            } else {
                echo "   ⚠️  Found " . count($permissionIssues) . " permission issues\n";
                $this->results['permission_issues'] = $permissionIssues;
            }
            
        } catch (Exception $e) {
            $this->errors['file_permissions'] = $e->getMessage();
            echo "   ❌ File permissions setup failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Create security directories
     */
    private function createSecurityDirectories() {
        echo "📁 Creating security directories...\n";
        
        $directories = [
            __DIR__ . '/logs' => 0750,
            __DIR__ . '/backups' => 0750,
            __DIR__ . '/config' => 0755
        ];
        
        foreach ($directories as $dir => $perms) {
            if (!is_dir($dir)) {
                mkdir($dir, $perms, true);
                echo "   ✓ Created directory: " . basename($dir) . "\n";
            }
            
            // Ensure correct permissions
            chmod($dir, $perms);
        }
        
        $this->results['security_directories'] = array_keys($directories);
    }
    
    /**
     * Setup .htaccess protection
     */
    private function setupHtaccessProtection() {
        echo "🛡️  Setting up .htaccess protection...\n";
        
        try {
            $htaccessResults = FilePermissions::createSecurityHtaccess();
            $this->results['htaccess_files'] = $htaccessResults;
            
            $successCount = count(array_filter($htaccessResults));
            $totalCount = count($htaccessResults);
            
            echo "   ✓ Created $successCount/$totalCount .htaccess files\n";
            
        } catch (Exception $e) {
            $this->errors['htaccess_setup'] = $e->getMessage();
            echo "   ❌ .htaccess setup failed: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Create security configuration files
     */
    private function createSecurityConfigs() {
        echo "⚙️  Creating security configuration files...\n";
        
        // Create security.ini for PHP settings
        $securityIni = __DIR__ . '/config/security.ini';
        $securitySettings = [
            'display_errors = Off',
            'log_errors = On',
            'error_log = ' . __DIR__ . '/logs/php_errors.log',
            'expose_php = Off',
            'allow_url_fopen = Off',
            'allow_url_include = Off',
            'session.cookie_httponly = 1',
            'session.cookie_secure = 1',
            'session.use_strict_mode = 1',
            'session.cookie_samesite = "Strict"'
        ];
        
        file_put_contents($securityIni, implode("\n", $securitySettings));
        chmod($securityIni, 0644);
        
        echo "   ✓ Created PHP security configuration\n";
        
        // Create database security configuration
        $dbSecurityConfig = __DIR__ . '/config/database_security.sql';
        $dbSecuritySQL = [
            "-- Database Security Configuration",
            "-- Create dedicated voting system user",
            "CREATE USER IF NOT EXISTS 'voting_user'@'localhost' IDENTIFIED BY 'secure_voting_password';",
            "",
            "-- Grant minimal required privileges",
            "GRANT SELECT, INSERT, UPDATE ON polltest.* TO 'voting_user'@'localhost';",
            "GRANT DELETE ON polltest.voters TO 'voting_user'@'localhost';",
            "",
            "-- Remove dangerous privileges",
            "REVOKE FILE, PROCESS, SUPER ON *.* FROM 'voting_user'@'localhost';",
            "",
            "-- Set secure SQL mode",
            "SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';",
            "",
            "-- Flush privileges",
            "FLUSH PRIVILEGES;"
        ];
        
        file_put_contents($dbSecurityConfig, implode("\n", $dbSecuritySQL));
        chmod($dbSecurityConfig, 0600);
        
        echo "   ✓ Created database security configuration\n";
        
        $this->results['security_configs'] = [$securityIni, $dbSecurityConfig];
    }
    
    /**
     * Validate system security
     */
    private function validateSystemSecurity() {
        echo "🔍 Validating system security...\n";
        
        $validationResults = [];
        
        // Check file permissions
        $permissionIssues = FilePermissions::auditPermissions();
        $validationResults['file_permissions'] = empty($permissionIssues) ? 'secure' : 'issues_found';
        
        // Check configuration files
        $configFiles = [
            __DIR__ . '/config/.env',
            __DIR__ . '/config/security.ini'
        ];
        
        foreach ($configFiles as $file) {
            $validationResults['config_files'][basename($file)] = file_exists($file) ? 'exists' : 'missing';
        }
        
        // Check .htaccess files
        $htaccessFiles = [
            __DIR__ . '/config/.htaccess',
            __DIR__ . '/logs/.htaccess',
            __DIR__ . '/includes/.htaccess'
        ];
        
        foreach ($htaccessFiles as $file) {
            $validationResults['htaccess_files'][basename(dirname($file))] = file_exists($file) ? 'protected' : 'unprotected';
        }
        
        $this->results['security_validation'] = $validationResults;
        
        // Count security measures
        $securityMeasures = 0;
        $totalMeasures = 0;
        
        foreach ($validationResults as $category => $results) {
            if (is_array($results)) {
                foreach ($results as $result) {
                    $totalMeasures++;
                    if (in_array($result, ['exists', 'protected', 'secure'])) {
                        $securityMeasures++;
                    }
                }
            } else {
                $totalMeasures++;
                if ($result === 'secure') {
                    $securityMeasures++;
                }
            }
        }
        
        echo "   ✓ Security validation: $securityMeasures/$totalMeasures measures implemented\n";
    }
    
    /**
     * Generate security report
     */
    private function generateSecurityReport() {
        echo "📊 Generating security report...\n";
        
        $reportFile = __DIR__ . '/logs/system_hardening_report.log';
        $report = [
            "System Hardening Report - " . date('Y-m-d H:i:s'),
            str_repeat("=", 50),
            "",
            "RESULTS:",
            json_encode($this->results, JSON_PRETTY_PRINT),
            "",
            "ERRORS:",
            json_encode($this->errors, JSON_PRETTY_PRINT),
            "",
            "RECOMMENDATIONS:",
            "1. Regularly review and update security configurations",
            "2. Monitor security logs for suspicious activities", 
            "3. Keep system components updated",
            "4. Perform regular security audits",
            "5. Test backup and recovery procedures",
            "",
            "Report generated by System Hardening Script"
        ];
        
        file_put_contents($reportFile, implode("\n", $report));
        chmod($reportFile, 0640);
        
        echo "   ✓ Security report saved to: $reportFile\n";
        
        $this->results['security_report'] = $reportFile;
    }
}

// Run system hardening if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $hardening = new SystemHardening();
    $results = $hardening->hardenSystem();
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "System Hardening Summary:\n";
    echo "- Configuration: " . (isset($results['config_validation']) ? '✅' : '❌') . "\n";
    echo "- File Permissions: " . (isset($results['file_permissions']) ? '✅' : '❌') . "\n";
    echo "- Directory Protection: " . (isset($results['htaccess_files']) ? '✅' : '❌') . "\n";
    echo "- Security Configs: " . (isset($results['security_configs']) ? '✅' : '❌') . "\n";
    echo str_repeat("=", 50) . "\n";
}
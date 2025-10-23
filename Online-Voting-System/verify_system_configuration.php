<?php
/**
 * System Configuration Security Verification Script
 * Verifies that all system configuration security measures are properly implemented
 */

require_once __DIR__ . '/includes/ConfigManager.php';
require_once __DIR__ . '/includes/FilePermissions.php';

class SystemConfigurationVerification {
    
    private $verificationResults = [];
    private $criticalIssues = [];
    private $warnings = [];
    
    /**
     * Run complete system configuration verification
     */
    public function verifySystemConfiguration() {
        echo "🔍 Verifying System Configuration Security Implementation...\n\n";
        
        // Verify configuration management
        $this->verifyConfigurationManagement();
        
        // Verify file permissions
        $this->verifyFilePermissions();
        
        // Verify directory protection
        $this->verifyDirectoryProtection();
        
        // Verify security configurations
        $this->verifySecurityConfigurations();
        
        // Verify system hardening
        $this->verifySystemHardening();
        
        // Generate verification report
        $this->generateVerificationReport();
        
        return [
            'results' => $this->verificationResults,
            'critical_issues' => $this->criticalIssues,
            'warnings' => $this->warnings
        ];
    }
    
    /**
     * Verify configuration management implementation
     */
    private function verifyConfigurationManagement() {
        echo "📋 Verifying Configuration Management...\n";
        
        // Check ConfigManager class exists and is functional
        if (class_exists('ConfigManager')) {
            echo "   ✅ ConfigManager class exists\n";
            $this->verificationResults['config_manager_class'] = 'implemented';
            
            // Test configuration loading
            try {
                ConfigManager::load(__DIR__ . '/config/.env.example');
                echo "   ✅ Configuration loading works\n";
                $this->verificationResults['config_loading'] = 'working';
            } catch (Exception $e) {
                echo "   ❌ Configuration loading failed: " . $e->getMessage() . "\n";
                $this->criticalIssues[] = "Configuration loading not working";
            }
            
            // Test configuration validation
            $errors = ConfigManager::validate();
            if (method_exists('ConfigManager', 'validate')) {
                echo "   ✅ Configuration validation implemented\n";
                $this->verificationResults['config_validation'] = 'implemented';
            }
            
            // Test database configuration
            if (method_exists('ConfigManager', 'getDatabaseConfig')) {
                $dbConfig = ConfigManager::getDatabaseConfig();
                if (is_array($dbConfig) && isset($dbConfig['host'])) {
                    echo "   ✅ Database configuration retrieval works\n";
                    $this->verificationResults['db_config_retrieval'] = 'working';
                } else {
                    echo "   ❌ Database configuration retrieval failed\n";
                    $this->criticalIssues[] = "Database configuration not working";
                }
            }
            
            // Test security configuration
            if (method_exists('ConfigManager', 'getSecurityConfig')) {
                $secConfig = ConfigManager::getSecurityConfig();
                if (is_array($secConfig)) {
                    echo "   ✅ Security configuration retrieval works\n";
                    $this->verificationResults['security_config_retrieval'] = 'working';
                } else {
                    echo "   ❌ Security configuration retrieval failed\n";
                    $this->criticalIssues[] = "Security configuration not working";
                }
            }
            
        } else {
            echo "   ❌ ConfigManager class not found\n";
            $this->criticalIssues[] = "ConfigManager class missing";
        }
    }
    
    /**
     * Verify file permissions implementation
     */
    private function verifyFilePermissions() {
        echo "🔐 Verifying File Permissions...\n";
        
        // Check FilePermissions class exists
        if (class_exists('FilePermissions')) {
            echo "   ✅ FilePermissions class exists\n";
            $this->verificationResults['file_permissions_class'] = 'implemented';
            
            // Check configuration file permissions
            $configFile = __DIR__ . '/config/.env.example';
            if (file_exists($configFile)) {
                $perms = fileperms($configFile) & 0777;
                if ($perms <= 0644) {
                    echo "   ✅ Configuration file permissions are secure\n";
                    $this->verificationResults['config_file_perms'] = 'secure';
                } else {
                    echo "   ⚠️  Configuration file permissions could be more secure: " . sprintf('%o', $perms) . "\n";
                    $this->warnings[] = "Configuration file permissions: " . sprintf('%o', $perms);
                }
            }
            
            // Check PHP file permissions
            $phpFiles = glob(__DIR__ . '/*.php');
            $insecureFiles = 0;
            
            foreach ($phpFiles as $file) {
                $perms = fileperms($file) & 0777;
                if ($perms > 0644) {
                    $insecureFiles++;
                }
            }
            
            if ($insecureFiles === 0) {
                echo "   ✅ All PHP files have secure permissions\n";
                $this->verificationResults['php_file_perms'] = 'secure';
            } else {
                echo "   ⚠️  Found $insecureFiles PHP files with insecure permissions\n";
                $this->warnings[] = "$insecureFiles PHP files with insecure permissions";
            }
            
            // Test permission audit functionality
            if (method_exists('FilePermissions', 'auditPermissions')) {
                $issues = FilePermissions::auditPermissions(__DIR__);
                if (empty($issues)) {
                    echo "   ✅ Permission audit shows no issues\n";
                    $this->verificationResults['permission_audit'] = 'clean';
                } else {
                    echo "   ⚠️  Permission audit found " . count($issues) . " issues\n";
                    $this->warnings[] = count($issues) . " permission issues found";
                }
            }
            
        } else {
            echo "   ❌ FilePermissions class not found\n";
            $this->criticalIssues[] = "FilePermissions class missing";
        }
    }
    
    /**
     * Verify directory protection implementation
     */
    private function verifyDirectoryProtection() {
        echo "🛡️  Verifying Directory Protection...\n";
        
        $protectedDirectories = [
            __DIR__ . '/config',
            __DIR__ . '/includes',
            __DIR__ . '/logs'
        ];
        
        $protectedCount = 0;
        
        foreach ($protectedDirectories as $dir) {
            $dirName = basename($dir);
            $htaccessFile = $dir . '/.htaccess';
            
            if (is_dir($dir)) {
                if (file_exists($htaccessFile)) {
                    $content = file_get_contents($htaccessFile);
                    if (strpos($content, 'Deny from all') !== false || strpos($content, 'Order deny,allow') !== false) {
                        echo "   ✅ $dirName directory is protected\n";
                        $protectedCount++;
                    } else {
                        echo "   ⚠️  $dirName directory .htaccess exists but may not be properly configured\n";
                        $this->warnings[] = "$dirName directory protection may be incomplete";
                    }
                } else {
                    echo "   ❌ $dirName directory is not protected (missing .htaccess)\n";
                    $this->criticalIssues[] = "$dirName directory not protected";
                }
            } else {
                echo "   ⚠️  $dirName directory does not exist\n";
                $this->warnings[] = "$dirName directory missing";
            }
        }
        
        $this->verificationResults['protected_directories'] = $protectedCount . '/' . count($protectedDirectories);
    }
    
    /**
     * Verify security configurations
     */
    private function verifySecurityConfigurations() {
        echo "⚙️  Verifying Security Configurations...\n";
        
        // Check secure connection file
        $secureConnectionFile = __DIR__ . '/secure_connection.php';
        if (file_exists($secureConnectionFile)) {
            echo "   ✅ Secure connection file exists\n";
            $this->verificationResults['secure_connection_file'] = 'exists';
            
            $content = file_get_contents($secureConnectionFile);
            $securityFeatures = [
                'ConfigManager::load' => 'Configuration management integration',
                'set_charset' => 'Character set security',
                'error_log' => 'Error logging',
                'sql_mode' => 'SQL mode security'
            ];
            
            foreach ($securityFeatures as $feature => $description) {
                if (strpos($content, $feature) !== false) {
                    echo "   ✅ $description implemented\n";
                } else {
                    echo "   ⚠️  $description not found\n";
                    $this->warnings[] = "$description missing in secure connection";
                }
            }
        } else {
            echo "   ❌ Secure connection file not found\n";
            $this->criticalIssues[] = "Secure connection file missing";
        }
        
        // Check environment configuration
        $envExampleFile = __DIR__ . '/config/.env.example';
        if (file_exists($envExampleFile)) {
            echo "   ✅ Environment configuration example exists\n";
            $this->verificationResults['env_example'] = 'exists';
            
            $content = file_get_contents($envExampleFile);
            $requiredConfigs = [
                'DB_HOST' => 'Database host configuration',
                'SESSION_TIMEOUT' => 'Session timeout configuration',
                'LOGIN_ATTEMPTS_LIMIT' => 'Login attempts limit',
                'CSP_ENABLED' => 'Content Security Policy setting'
            ];
            
            foreach ($requiredConfigs as $config => $description) {
                if (strpos($content, $config) !== false) {
                    echo "   ✅ $description present\n";
                } else {
                    echo "   ⚠️  $description missing\n";
                    $this->warnings[] = "$description missing in environment config";
                }
            }
        } else {
            echo "   ❌ Environment configuration example not found\n";
            $this->criticalIssues[] = "Environment configuration example missing";
        }
    }
    
    /**
     * Verify system hardening implementation
     */
    private function verifySystemHardening() {
        echo "🔧 Verifying System Hardening...\n";
        
        // Check system hardening checklist
        $hardeningChecklist = __DIR__ . '/SYSTEM_HARDENING_CHECKLIST.md';
        if (file_exists($hardeningChecklist)) {
            echo "   ✅ System hardening checklist exists\n";
            $this->verificationResults['hardening_checklist'] = 'exists';
        } else {
            echo "   ❌ System hardening checklist not found\n";
            $this->criticalIssues[] = "System hardening checklist missing";
        }
        
        // Check system hardening setup script
        $hardeningSetup = __DIR__ . '/setup_system_hardening.php';
        if (file_exists($hardeningSetup)) {
            echo "   ✅ System hardening setup script exists\n";
            $this->verificationResults['hardening_setup'] = 'exists';
        } else {
            echo "   ❌ System hardening setup script not found\n";
            $this->criticalIssues[] = "System hardening setup script missing";
        }
        
        // Check if logs directory exists and is properly configured
        $logsDir = __DIR__ . '/logs';
        if (is_dir($logsDir)) {
            $perms = fileperms($logsDir) & 0777;
            if ($perms <= 0755) {
                echo "   ✅ Logs directory has secure permissions\n";
                $this->verificationResults['logs_dir_perms'] = 'secure';
            } else {
                echo "   ⚠️  Logs directory permissions could be more secure: " . sprintf('%o', $perms) . "\n";
                $this->warnings[] = "Logs directory permissions: " . sprintf('%o', $perms);
            }
        } else {
            echo "   ⚠️  Logs directory does not exist\n";
            $this->warnings[] = "Logs directory missing";
        }
        
        // Check configuration directory
        $configDir = __DIR__ . '/config';
        if (is_dir($configDir)) {
            echo "   ✅ Configuration directory exists\n";
            $this->verificationResults['config_dir'] = 'exists';
        } else {
            echo "   ❌ Configuration directory not found\n";
            $this->criticalIssues[] = "Configuration directory missing";
        }
    }
    
    /**
     * Generate verification report
     */
    private function generateVerificationReport() {
        echo "\n📊 Generating Verification Report...\n";
        
        $totalChecks = count($this->verificationResults);
        $criticalCount = count($this->criticalIssues);
        $warningCount = count($this->warnings);
        
        $report = [
            "System Configuration Security Verification Report",
            "Generated: " . date('Y-m-d H:i:s'),
            str_repeat("=", 60),
            "",
            "VERIFICATION SUMMARY:",
            "Total Checks: $totalChecks",
            "Critical Issues: $criticalCount",
            "Warnings: $warningCount",
            "",
            "VERIFICATION RESULTS:",
            ""
        ];
        
        foreach ($this->verificationResults as $check => $result) {
            $report[] = "✓ $check: $result";
        }
        
        if (!empty($this->criticalIssues)) {
            $report[] = "";
            $report[] = "CRITICAL ISSUES:";
            foreach ($this->criticalIssues as $issue) {
                $report[] = "❌ $issue";
            }
        }
        
        if (!empty($this->warnings)) {
            $report[] = "";
            $report[] = "WARNINGS:";
            foreach ($this->warnings as $warning) {
                $report[] = "⚠️  $warning";
            }
        }
        
        $report[] = "";
        $report[] = "COMPLIANCE STATUS:";
        
        if ($criticalCount === 0) {
            $report[] = "✅ COMPLIANT - All critical security measures are implemented";
        } else {
            $report[] = "❌ NON-COMPLIANT - Critical issues must be resolved";
        }
        
        $report[] = "";
        $report[] = "NEXT STEPS:";
        
        if ($criticalCount > 0) {
            $report[] = "1. Resolve all critical issues immediately";
            $report[] = "2. Review and address warnings";
            $report[] = "3. Re-run verification after fixes";
        } else if ($warningCount > 0) {
            $report[] = "1. Review and address warnings for optimal security";
            $report[] = "2. Continue regular security monitoring";
        } else {
            $report[] = "1. System configuration security is properly implemented";
            $report[] = "2. Continue regular security audits";
            $report[] = "3. Monitor for configuration changes";
        }
        
        // Save report
        $reportFile = __DIR__ . '/logs/system_config_verification_report.log';
        if (!is_dir(dirname($reportFile))) {
            mkdir(dirname($reportFile), 0755, true);
        }
        
        file_put_contents($reportFile, implode("\n", $report));
        chmod($reportFile, 0640);
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Verification Complete!\n";
        echo "Critical Issues: $criticalCount\n";
        echo "Warnings: $warningCount\n";
        echo "Status: " . ($criticalCount === 0 ? "COMPLIANT ✅" : "NON-COMPLIANT ❌") . "\n";
        echo "Report saved to: $reportFile\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run verification if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $verifier = new SystemConfigurationVerification();
    $results = $verifier->verifySystemConfiguration();
}
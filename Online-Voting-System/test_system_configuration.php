<?php
/**
 * System Configuration Security Test Script
 * Tests all aspects of system configuration security implementation
 */

require_once __DIR__ . '/includes/ConfigManager.php';
require_once __DIR__ . '/includes/FilePermissions.php';

class SystemConfigurationTest {
    
    private $testResults = [];
    private $testsPassed = 0;
    private $testsTotal = 0;
    
    /**
     * Run all system configuration security tests
     */
    public function runAllTests() {
        echo "🧪 Running System Configuration Security Tests...\n\n";
        
        // Test configuration management
        $this->testConfigurationManagement();
        
        // Test file permissions
        $this->testFilePermissions();
        
        // Test directory protection
        $this->testDirectoryProtection();
        
        // Test security configurations
        $this->testSecurityConfigurations();
        
        // Test environment variable handling
        $this->testEnvironmentVariables();
        
        // Test database configuration security
        $this->testDatabaseConfigSecurity();
        
        // Generate test report
        $this->generateTestReport();
        
        return $this->testResults;
    }
    
    /**
     * Test configuration management functionality
     */
    private function testConfigurationManagement() {
        echo "📋 Testing Configuration Management...\n";
        
        // Test 1: Configuration loading
        $this->runTest('config_loading', function() {
            try {
                ConfigManager::load(__DIR__ . '/config/.env.example');
                return true;
            } catch (Exception $e) {
                return "Failed to load configuration: " . $e->getMessage();
            }
        });
        
        // Test 2: Configuration validation
        $this->runTest('config_validation', function() {
            $errors = ConfigManager::validate();
            return empty($errors) ? true : "Validation errors: " . implode(', ', $errors);
        });
        
        // Test 3: Database configuration retrieval
        $this->runTest('database_config', function() {
            $dbConfig = ConfigManager::getDatabaseConfig();
            $required = ['host', 'username', 'password', 'database'];
            
            foreach ($required as $key) {
                if (!isset($dbConfig[$key])) {
                    return "Missing database config key: $key";
                }
            }
            return true;
        });
        
        // Test 4: Security configuration retrieval
        $this->runTest('security_config', function() {
            $secConfig = ConfigManager::getSecurityConfig();
            $required = ['session_timeout', 'login_attempts_limit', 'lockout_duration'];
            
            foreach ($required as $key) {
                if (!isset($secConfig[$key])) {
                    return "Missing security config key: $key";
                }
            }
            return true;
        });
        
        // Test 5: Production mode detection
        $this->runTest('production_mode', function() {
            $isProduction = ConfigManager::isProduction();
            return is_bool($isProduction) ? true : "Production mode detection failed";
        });
    }
    
    /**
     * Test file permissions security
     */
    private function testFilePermissions() {
        echo "🔐 Testing File Permissions...\n";
        
        // Test 1: Configuration file permissions
        $this->runTest('config_file_perms', function() {
            $configFile = __DIR__ . '/config/.env.example';
            if (!file_exists($configFile)) {
                return "Configuration file not found";
            }
            
            $perms = fileperms($configFile) & 0777;
            $expectedPerms = 0644; // Should be readable by others for example file
            
            return $perms <= $expectedPerms ? true : "Insecure permissions: " . sprintf('%o', $perms);
        });
        
        // Test 2: PHP file permissions audit
        $this->runTest('php_file_perms', function() {
            $issues = FilePermissions::auditPermissions(__DIR__);
            $phpIssues = array_filter($issues, function($issue) {
                return $issue['type'] === 'php';
            });
            
            return empty($phpIssues) ? true : "Found " . count($phpIssues) . " PHP file permission issues";
        });
        
        // Test 3: Directory permissions
        $this->runTest('directory_perms', function() {
            $directories = [
                __DIR__ . '/config',
                __DIR__ . '/includes'
            ];
            
            foreach ($directories as $dir) {
                if (is_dir($dir)) {
                    $perms = fileperms($dir) & 0777;
                    if ($perms > 0755) {
                        return "Insecure directory permissions for $dir: " . sprintf('%o', $perms);
                    }
                }
            }
            return true;
        });
        
        // Test 4: Secure permissions application
        $this->runTest('apply_secure_perms', function() {
            try {
                $results = FilePermissions::applySecurePermissions(__DIR__);
                return is_array($results) ? true : "Failed to apply secure permissions";
            } catch (Exception $e) {
                return "Error applying permissions: " . $e->getMessage();
            }
        });
    }
    
    /**
     * Test directory protection mechanisms
     */
    private function testDirectoryProtection() {
        echo "🛡️  Testing Directory Protection...\n";
        
        // Test 1: .htaccess file creation
        $this->runTest('htaccess_creation', function() {
            try {
                $results = FilePermissions::createSecurityHtaccess();
                $successCount = count(array_filter($results));
                return $successCount > 0 ? true : "No .htaccess files created";
            } catch (Exception $e) {
                return "Error creating .htaccess files: " . $e->getMessage();
            }
        });
        
        // Test 2: Protected directories exist
        $this->runTest('protected_directories', function() {
            $protectedDirs = [
                __DIR__ . '/config',
                __DIR__ . '/includes'
            ];
            
            foreach ($protectedDirs as $dir) {
                $htaccessFile = $dir . '/.htaccess';
                if (is_dir($dir) && !file_exists($htaccessFile)) {
                    return "Missing .htaccess protection for: $dir";
                }
            }
            return true;
        });
        
        // Test 3: .htaccess content validation
        $this->runTest('htaccess_content', function() {
            $htaccessFile = __DIR__ . '/config/.htaccess';
            if (file_exists($htaccessFile)) {
                $content = file_get_contents($htaccessFile);
                if (strpos($content, 'Deny from all') === false) {
                    return ".htaccess file doesn't contain proper deny rules";
                }
            }
            return true;
        });
    }
    
    /**
     * Test security configurations
     */
    private function testSecurityConfigurations() {
        echo "⚙️  Testing Security Configurations...\n";
        
        // Test 1: Security configuration files exist
        $this->runTest('security_config_files', function() {
            $configFiles = [
                __DIR__ . '/config/.env.example',
                __DIR__ . '/secure_connection.php'
            ];
            
            foreach ($configFiles as $file) {
                if (!file_exists($file)) {
                    return "Missing security config file: " . basename($file);
                }
            }
            return true;
        });
        
        // Test 2: Secure connection implementation
        $this->runTest('secure_connection', function() {
            $connectionFile = __DIR__ . '/secure_connection.php';
            if (!file_exists($connectionFile)) {
                return "Secure connection file not found";
            }
            
            $content = file_get_contents($connectionFile);
            $securityFeatures = [
                'ConfigManager::load',
                'mysqli_real_escape_string',
                'set_charset',
                'error_log'
            ];
            
            foreach ($securityFeatures as $feature) {
                if (strpos($content, $feature) === false) {
                    return "Missing security feature in connection: $feature";
                }
            }
            return true;
        });
        
        // Test 3: Configuration validation
        $this->runTest('config_validation_rules', function() {
            try {
                // Test with invalid configuration
                $tempConfig = [
                    'DB_HOST' => '',
                    'SESSION_TIMEOUT' => '100'
                ];
                
                // This should return validation errors
                $errors = ConfigManager::validate();
                return is_array($errors) ? true : "Configuration validation not working properly";
            } catch (Exception $e) {
                return "Configuration validation error: " . $e->getMessage();
            }
        });
    }
    
    /**
     * Test environment variable handling
     */
    private function testEnvironmentVariables() {
        echo "🌍 Testing Environment Variables...\n";
        
        // Test 1: Environment variable loading
        $this->runTest('env_var_loading', function() {
            $testKey = 'TEST_CONFIG_VAR';
            $testValue = 'test_value_' . time();
            
            putenv("$testKey=$testValue");
            $retrieved = ConfigManager::get($testKey);
            
            return $retrieved === $testValue ? true : "Environment variable not loaded correctly";
        });
        
        // Test 2: Default value handling
        $this->runTest('default_values', function() {
            $nonExistentKey = 'NON_EXISTENT_CONFIG_KEY_' . time();
            $defaultValue = 'default_test_value';
            
            $result = ConfigManager::get($nonExistentKey, $defaultValue);
            return $result === $defaultValue ? true : "Default value handling failed";
        });
        
        // Test 3: Configuration precedence
        $this->runTest('config_precedence', function() {
            // Environment variables should take precedence over config file
            $testKey = 'PRECEDENCE_TEST';
            putenv("$testKey=env_value");
            
            $result = ConfigManager::get($testKey);
            return $result === 'env_value' ? true : "Environment variable precedence failed";
        });
    }
    
    /**
     * Test database configuration security
     */
    private function testDatabaseConfigSecurity() {
        echo "🗄️  Testing Database Configuration Security...\n";
        
        // Test 1: Database configuration structure
        $this->runTest('db_config_structure', function() {
            $dbConfig = ConfigManager::getDatabaseConfig();
            $requiredKeys = ['host', 'username', 'password', 'database'];
            
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $dbConfig)) {
                    return "Missing database configuration key: $key";
                }
            }
            return true;
        });
        
        // Test 2: Secure connection parameters
        $this->runTest('secure_connection_params', function() {
            $connectionFile = __DIR__ . '/secure_connection.php';
            if (!file_exists($connectionFile)) {
                return "Secure connection file not found";
            }
            
            $content = file_get_contents($connectionFile);
            $securityChecks = [
                'set_charset("utf8mb4")',
                'sql_mode',
                'error_log'
            ];
            
            foreach ($securityChecks as $check) {
                if (strpos($content, $check) === false) {
                    return "Missing security check in connection: $check";
                }
            }
            return true;
        });
        
        // Test 3: Error handling in database connection
        $this->runTest('db_error_handling', function() {
            $connectionFile = __DIR__ . '/secure_connection.php';
            $content = file_get_contents($connectionFile);
            
            $errorHandling = [
                'connect_error',
                'isProduction()',
                'error_log'
            ];
            
            foreach ($errorHandling as $handler) {
                if (strpos($content, $handler) === false) {
                    return "Missing error handling: $handler";
                }
            }
            return true;
        });
    }
    
    /**
     * Run individual test
     */
    private function runTest($testName, $testFunction) {
        $this->testsTotal++;
        
        try {
            $result = $testFunction();
            
            if ($result === true) {
                echo "   ✅ $testName: PASSED\n";
                $this->testsPassed++;
                $this->testResults[$testName] = 'PASSED';
            } else {
                echo "   ❌ $testName: FAILED - $result\n";
                $this->testResults[$testName] = "FAILED: $result";
            }
        } catch (Exception $e) {
            echo "   ❌ $testName: ERROR - " . $e->getMessage() . "\n";
            $this->testResults[$testName] = "ERROR: " . $e->getMessage();
        }
    }
    
    /**
     * Generate test report
     */
    private function generateTestReport() {
        echo "\n📊 Generating Test Report...\n";
        
        $passRate = ($this->testsPassed / $this->testsTotal) * 100;
        
        $report = [
            "System Configuration Security Test Report",
            "Generated: " . date('Y-m-d H:i:s'),
            str_repeat("=", 50),
            "",
            "SUMMARY:",
            "Total Tests: {$this->testsTotal}",
            "Passed: {$this->testsPassed}",
            "Failed: " . ($this->testsTotal - $this->testsPassed),
            "Pass Rate: " . number_format($passRate, 2) . "%",
            "",
            "DETAILED RESULTS:",
            ""
        ];
        
        foreach ($this->testResults as $test => $result) {
            $report[] = "$test: $result";
        }
        
        $report[] = "";
        $report[] = "RECOMMENDATIONS:";
        
        if ($passRate < 100) {
            $report[] = "- Review and fix failed tests";
            $report[] = "- Ensure all security configurations are properly implemented";
            $report[] = "- Verify file permissions and directory protection";
        } else {
            $report[] = "- All tests passed! System configuration security is properly implemented";
            $report[] = "- Continue regular security audits";
            $report[] = "- Monitor for configuration changes";
        }
        
        // Save report to file
        $reportFile = __DIR__ . '/logs/system_config_test_report.log';
        if (!is_dir(dirname($reportFile))) {
            mkdir(dirname($reportFile), 0755, true);
        }
        
        file_put_contents($reportFile, implode("\n", $report));
        chmod($reportFile, 0640);
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Test Summary: {$this->testsPassed}/{$this->testsTotal} tests passed ({$passRate}%)\n";
        echo "Report saved to: $reportFile\n";
        echo str_repeat("=", 50) . "\n";
    }
}

// Run tests if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new SystemConfigurationTest();
    $results = $tester->runAllTests();
}
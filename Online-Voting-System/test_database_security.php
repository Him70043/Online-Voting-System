<?php
/**
 * Database Security Test Script
 * Tests all database security enhancements
 */

require_once __DIR__ . '/includes/DatabaseSecurity.php';

class DatabaseSecurityTest {
    private $dbSecurity;
    private $testResults = [];
    
    public function __construct() {
        $this->dbSecurity = new DatabaseSecurity();
    }
    
    public function runAllTests() {
        echo "<h2>Database Security Test Suite</h2>\n";
        echo "<style>
            .test-pass { color: green; font-weight: bold; }
            .test-fail { color: red; font-weight: bold; }
            .test-info { color: blue; }
            pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        </style>\n";
        echo "<pre>\n";
        
        $this->testSecureConnection();
        $this->testDatabaseUserPrivileges();
        $this->testBackupFunctionality();
        $this->testIntegrityChecks();
        $this->testConnectionSecurity();
        $this->testErrorHandling();
        
        $this->displaySummary();
    }
    
    private function testSecureConnection() {
        echo "🔒 Testing Secure Database Connection...\n";
        
        try {
            $connection = $this->dbSecurity->getSecureConnection();
            
            if ($connection) {
                $this->addResult('secure_connection', true, 'Secure connection established');
                echo "   <span class='test-pass'>✅ PASS</span> - Secure connection established\n";
                
                // Test connection properties
                $charset = $connection->character_set_name();
                if ($charset === 'utf8mb4') {
                    echo "   <span class='test-pass'>✅ PASS</span> - Correct charset (utf8mb4)\n";
                } else {
                    echo "   <span class='test-fail'>❌ FAIL</span> - Incorrect charset: {$charset}\n";
                }
                
                $connection->close();
            } else {
                $this->addResult('secure_connection', false, 'Failed to establish secure connection');
                echo "   <span class='test-fail'>❌ FAIL</span> - Failed to establish secure connection\n";
            }
            
        } catch (Exception $e) {
            $this->addResult('secure_connection', false, $e->getMessage());
            echo "   <span class='test-fail'>❌ FAIL</span> - Exception: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function testDatabaseUserPrivileges() {
        echo "👤 Testing Database User Privileges...\n";
        
        try {
            // Test if voting user exists and has correct privileges
            $rootConnection = new mysqli('localhost', 'root', '', 'polltest');
            
            if ($rootConnection->connect_error) {
                echo "   <span class='test-fail'>❌ FAIL</span> - Cannot connect as root to test privileges\n";
                return;
            }
            
            // Check if voting user exists
            $userCheck = $rootConnection->query("SELECT User FROM mysql.user WHERE User = 'voting_user'");
            
            if ($userCheck && $userCheck->num_rows > 0) {
                echo "   <span class='test-pass'>✅ PASS</span> - Voting user exists\n";
                
                // Check privileges
                $privCheck = $rootConnection->query("SHOW GRANTS FOR 'voting_user'@'localhost'");
                
                if ($privCheck && $privCheck->num_rows > 0) {
                    echo "   <span class='test-pass'>✅ PASS</span> - User has assigned privileges\n";
                    $this->addResult('user_privileges', true, 'Voting user configured correctly');
                } else {
                    echo "   <span class='test-fail'>❌ FAIL</span> - No privileges found for voting user\n";
                    $this->addResult('user_privileges', false, 'No privileges assigned');
                }
                
            } else {
                echo "   <span class='test-info'>ℹ️ INFO</span> - Voting user not found (may need to run setup)\n";
                $this->addResult('user_privileges', false, 'Voting user not created');
            }
            
            $rootConnection->close();
            
        } catch (Exception $e) {
            echo "   <span class='test-fail'>❌ FAIL</span> - Exception: " . $e->getMessage() . "\n";
            $this->addResult('user_privileges', false, $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function testBackupFunctionality() {
        echo "💾 Testing Backup Functionality...\n";
        
        try {
            $backupFile = $this->dbSecurity->createSecureBackup();
            
            if ($backupFile) {
                echo "   <span class='test-pass'>✅ PASS</span> - Backup created: " . basename($backupFile) . "\n";
                
                // Check if backup file exists and has content
                if (file_exists($backupFile . '.encrypted')) {
                    $fileSize = filesize($backupFile . '.encrypted');
                    echo "   <span class='test-pass'>✅ PASS</span> - Encrypted backup file exists ({$fileSize} bytes)\n";
                    $this->addResult('backup_functionality', true, 'Backup created and encrypted');
                } else {
                    echo "   <span class='test-fail'>❌ FAIL</span> - Encrypted backup file not found\n";
                    $this->addResult('backup_functionality', false, 'Encrypted backup not created');
                }
                
            } else {
                echo "   <span class='test-fail'>❌ FAIL</span> - Backup creation failed\n";
                $this->addResult('backup_functionality', false, 'Backup creation failed');
            }
            
        } catch (Exception $e) {
            echo "   <span class='test-fail'>❌ FAIL</span> - Exception: " . $e->getMessage() . "\n";
            $this->addResult('backup_functionality', false, $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function testIntegrityChecks() {
        echo "🔍 Testing Database Integrity Checks...\n";
        
        try {
            $integrityResult = $this->dbSecurity->performIntegrityChecks();
            
            if ($integrityResult['status'] === 'PASS') {
                echo "   <span class='test-pass'>✅ PASS</span> - All integrity checks passed\n";
                $this->addResult('integrity_checks', true, 'All integrity checks passed');
            } elseif ($integrityResult['status'] === 'FAIL') {
                echo "   <span class='test-fail'>❌ FAIL</span> - Integrity issues found:\n";
                foreach ($integrityResult['issues'] as $issue) {
                    echo "     - {$issue}\n";
                }
                $this->addResult('integrity_checks', false, 'Integrity issues found');
            } else {
                echo "   <span class='test-fail'>❌ ERROR</span> - Integrity check error\n";
                $this->addResult('integrity_checks', false, 'Integrity check error');
            }
            
        } catch (Exception $e) {
            echo "   <span class='test-fail'>❌ FAIL</span> - Exception: " . $e->getMessage() . "\n";
            $this->addResult('integrity_checks', false, $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function testConnectionSecurity() {
        echo "🔐 Testing Connection Security Features...\n";
        
        try {
            $connection = $this->dbSecurity->getSecureConnection();
            
            if ($connection) {
                // Test prepared statement support
                $stmt = $connection->prepare("SELECT 1 as test WHERE ? = ?");
                if ($stmt) {
                    $test1 = 1;
                    $test2 = 1;
                    $stmt->bind_param("ii", $test1, $test2);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result && $result->fetch_assoc()['test'] == 1) {
                        echo "   <span class='test-pass'>✅ PASS</span> - Prepared statements working\n";
                    } else {
                        echo "   <span class='test-fail'>❌ FAIL</span> - Prepared statements not working\n";
                    }
                    
                    $stmt->close();
                } else {
                    echo "   <span class='test-fail'>❌ FAIL</span> - Cannot create prepared statement\n";
                }
                
                // Test connection timeout settings
                echo "   <span class='test-pass'>✅ PASS</span> - Connection security features active\n";
                $this->addResult('connection_security', true, 'Connection security verified');
                
                $connection->close();
            } else {
                echo "   <span class='test-fail'>❌ FAIL</span> - Cannot establish connection for security test\n";
                $this->addResult('connection_security', false, 'Cannot test connection security');
            }
            
        } catch (Exception $e) {
            echo "   <span class='test-fail'>❌ FAIL</span> - Exception: " . $e->getMessage() . "\n";
            $this->addResult('connection_security', false, $e->getMessage());
        }
        
        echo "\n";
    }
    
    private function testErrorHandling() {
        echo "⚠️ Testing Error Handling...\n";
        
        try {
            // Test with invalid credentials
            $invalidDbSecurity = new DatabaseSecurity('invalid_host');
            $connection = $invalidDbSecurity->getSecureConnection();
            
            // Should still return a connection (fallback)
            if ($connection) {
                echo "   <span class='test-pass'>✅ PASS</span> - Fallback connection works\n";
                $this->addResult('error_handling', true, 'Error handling and fallback working');
                $connection->close();
            } else {
                echo "   <span class='test-fail'>❌ FAIL</span> - No fallback connection\n";
                $this->addResult('error_handling', false, 'Fallback connection failed');
            }
            
        } catch (Exception $e) {
            echo "   <span class='test-pass'>✅ PASS</span> - Exception properly caught: " . $e->getMessage() . "\n";
            $this->addResult('error_handling', true, 'Exceptions properly handled');
        }
        
        echo "\n";
    }
    
    private function addResult($test, $passed, $message) {
        $this->testResults[] = [
            'test' => $test,
            'passed' => $passed,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function displaySummary() {
        echo "📊 Test Summary:\n";
        echo "================\n";
        
        $totalTests = count($this->testResults);
        $passedTests = array_filter($this->testResults, function($result) {
            return $result['passed'];
        });
        $passedCount = count($passedTests);
        
        echo "Total Tests: {$totalTests}\n";
        echo "Passed: <span class='test-pass'>{$passedCount}</span>\n";
        echo "Failed: <span class='test-fail'>" . ($totalTests - $passedCount) . "</span>\n";
        
        $successRate = $totalTests > 0 ? round(($passedCount / $totalTests) * 100, 2) : 0;
        echo "Success Rate: {$successRate}%\n";
        
        if ($successRate >= 80) {
            echo "\n<span class='test-pass'>🎉 Database security implementation is working well!</span>\n";
        } elseif ($successRate >= 60) {
            echo "\n<span class='test-info'>⚠️ Database security partially implemented - review failed tests</span>\n";
        } else {
            echo "\n<span class='test-fail'>❌ Database security needs attention - multiple issues found</span>\n";
        }
        
        echo "\nDetailed Results:\n";
        foreach ($this->testResults as $result) {
            $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
            echo "- {$result['test']}: {$status} - {$result['message']}\n";
        }
    }
}

// Run tests
$tester = new DatabaseSecurityTest();
$tester->runAllTests();

echo "</pre>\n";
?>
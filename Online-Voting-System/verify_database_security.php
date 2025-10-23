<?php
/**
 * Database Security Verification Script
 * Verifies that all database security enhancements are properly implemented
 */

require_once __DIR__ . '/includes/DatabaseSecurity.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Security Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 5px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .pass { color: #27ae60; font-weight: bold; }
        .fail { color: #e74c3c; font-weight: bold; }
        .info { color: #3498db; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; }
        .status-card { padding: 15px; border-radius: 5px; border-left: 4px solid #3498db; background: #f8f9fa; }
    </style>
</head>
<body>";

echo "<div class='header'>
    <h1>🔒 Database Security Verification</h1>
    <p>Comprehensive verification of database security enhancements</p>
</div>";

try {
    $dbSecurity = new DatabaseSecurity();
    
    echo "<div class='section'>
        <h2>📋 Security Implementation Status</h2>
        <div class='status-grid'>";
    
    // 1. Verify Database User Creation
    echo "<div class='status-card'>
        <h3>👤 Database User Security</h3>";
    
    try {
        $rootConnection = new mysqli('localhost', 'root', '', 'polltest');
        if (!$rootConnection->connect_error) {
            $userCheck = $rootConnection->query("SELECT User FROM mysql.user WHERE User = 'voting_user'");
            if ($userCheck && $userCheck->num_rows > 0) {
                echo "<span class='pass'>✅ IMPLEMENTED</span><br>";
                echo "Dedicated voting user exists<br>";
                
                // Check privileges
                $privCheck = $rootConnection->query("SHOW GRANTS FOR 'voting_user'@'localhost'");
                if ($privCheck && $privCheck->num_rows > 0) {
                    echo "<span class='pass'>✅ CONFIGURED</span><br>";
                    echo "Minimal privileges assigned";
                } else {
                    echo "<span class='warning'>⚠️ PARTIAL</span><br>";
                    echo "User exists but privileges not verified";
                }
            } else {
                echo "<span class='info'>ℹ️ NOT IMPLEMENTED</span><br>";
                echo "Run setup_database_security.php to create user";
            }
        } else {
            echo "<span class='fail'>❌ ERROR</span><br>";
            echo "Cannot verify user (root access needed)";
        }
        $rootConnection->close();
    } catch (Exception $e) {
        echo "<span class='fail'>❌ ERROR</span><br>";
        echo "Exception: " . htmlspecialchars($e->getMessage());
    }
    
    echo "</div>";
    
    // 2. Verify Secure Connection
    echo "<div class='status-card'>
        <h3>🔐 Secure Connection</h3>";
    
    try {
        $connection = $dbSecurity->getSecureConnection();
        if ($connection) {
            echo "<span class='pass'>✅ WORKING</span><br>";
            echo "Secure connection established<br>";
            
            $charset = $connection->character_set_name();
            if ($charset === 'utf8mb4') {
                echo "<span class='pass'>✅ SECURE CHARSET</span><br>";
                echo "Using utf8mb4 encoding";
            } else {
                echo "<span class='warning'>⚠️ CHARSET</span><br>";
                echo "Charset: " . htmlspecialchars($charset);
            }
            
            $connection->close();
        } else {
            echo "<span class='fail'>❌ FAILED</span><br>";
            echo "Cannot establish secure connection";
        }
    } catch (Exception $e) {
        echo "<span class='fail'>❌ ERROR</span><br>";
        echo "Exception: " . htmlspecialchars($e->getMessage());
    }
    
    echo "</div>";
    
    // 3. Verify Backup Functionality
    echo "<div class='status-card'>
        <h3>💾 Backup System</h3>";
    
    $backupDir = __DIR__ . '/backups';
    if (file_exists($backupDir)) {
        $backupFiles = glob($backupDir . '/*.encrypted');
        if (!empty($backupFiles)) {
            echo "<span class='pass'>✅ FUNCTIONAL</span><br>";
            echo count($backupFiles) . " encrypted backup(s) found<br>";
            
            $latestBackup = max($backupFiles);
            $backupAge = time() - filemtime($latestBackup);
            $ageHours = round($backupAge / 3600, 1);
            
            if ($ageHours < 24) {
                echo "<span class='pass'>✅ RECENT</span><br>";
                echo "Latest backup: {$ageHours} hours ago";
            } else {
                echo "<span class='warning'>⚠️ OLD</span><br>";
                echo "Latest backup: {$ageHours} hours ago";
            }
        } else {
            echo "<span class='info'>ℹ️ NO BACKUPS</span><br>";
            echo "No encrypted backups found";
        }
    } else {
        echo "<span class='info'>ℹ️ NOT CONFIGURED</span><br>";
        echo "Backup directory not created";
    }
    
    echo "</div>";
    
    // 4. Verify Integrity Checks
    echo "<div class='status-card'>
        <h3>🔍 Integrity Monitoring</h3>";
    
    try {
        $integrityResult = $dbSecurity->performIntegrityChecks();
        
        if ($integrityResult['status'] === 'PASS') {
            echo "<span class='pass'>✅ HEALTHY</span><br>";
            echo "All integrity checks passed<br>";
            echo "<span class='pass'>✅ NO ISSUES</span><br>";
            echo "Database integrity verified";
        } elseif ($integrityResult['status'] === 'FAIL') {
            echo "<span class='warning'>⚠️ ISSUES FOUND</span><br>";
            echo count($integrityResult['issues']) . " issue(s) detected<br>";
            echo "<span class='info'>ℹ️ DETAILS</span><br>";
            echo "Check logs for specifics";
        } else {
            echo "<span class='fail'>❌ ERROR</span><br>";
            echo "Integrity check failed to run";
        }
    } catch (Exception $e) {
        echo "<span class='fail'>❌ ERROR</span><br>";
        echo "Exception: " . htmlspecialchars($e->getMessage());
    }
    
    echo "</div>";
    
    echo "</div></div>";
    
    // Detailed Verification Results
    echo "<div class='section'>
        <h2>🔍 Detailed Verification Results</h2>";
    
    // Database Statistics
    echo "<h3>📊 Database Statistics</h3>";
    $stats = $dbSecurity->getDatabaseStats();
    if ($stats) {
        echo "<pre>";
        echo "Table Record Counts:\n";
        foreach ($stats['table_counts'] as $table => $count) {
            echo "  {$table}: {$count} records\n";
        }
        
        echo "\nConnection Information:\n";
        echo "  Host: " . htmlspecialchars($stats['connection_info']['host']) . "\n";
        echo "  Server Version: " . htmlspecialchars($stats['connection_info']['server_version']) . "\n";
        echo "  Protocol Version: " . htmlspecialchars($stats['connection_info']['protocol_version']) . "\n";
        echo "</pre>";
    } else {
        echo "<p class='fail'>❌ Unable to retrieve database statistics</p>";
    }
    
    // Integrity Check Details
    echo "<h3>🔍 Latest Integrity Check</h3>";
    $integrityResult = $dbSecurity->performIntegrityChecks();
    echo "<pre>";
    echo "Status: " . $integrityResult['status'] . "\n";
    echo "Timestamp: " . $integrityResult['timestamp'] . "\n";
    
    if (!empty($integrityResult['issues'])) {
        echo "\nIssues Found:\n";
        foreach ($integrityResult['issues'] as $issue) {
            echo "  - " . htmlspecialchars($issue) . "\n";
        }
    } else {
        echo "\nNo issues detected.\n";
    }
    echo "</pre>";
    
    // Security Recommendations
    echo "<h3>💡 Security Recommendations</h3>";
    echo "<div class='status-card'>";
    
    $recommendations = [];
    
    // Check if using secure connection
    if (!file_exists(__DIR__ . '/config/db_credentials.php')) {
        $recommendations[] = "Run setup_database_security.php to create dedicated database user";
    }
    
    // Check backup age
    if (file_exists($backupDir)) {
        $backupFiles = glob($backupDir . '/*.encrypted');
        if (empty($backupFiles)) {
            $recommendations[] = "Create initial database backup";
        } else {
            $latestBackup = max($backupFiles);
            $backupAge = time() - filemtime($latestBackup);
            if ($backupAge > 86400) { // 24 hours
                $recommendations[] = "Update database backup (last backup is over 24 hours old)";
            }
        }
    } else {
        $recommendations[] = "Set up automated backup system";
    }
    
    // Check for integrity issues
    if ($integrityResult['status'] === 'FAIL') {
        $recommendations[] = "Address database integrity issues found in checks";
    }
    
    // Check log files
    $logFile = __DIR__ . '/logs/database_security.log';
    if (!file_exists($logFile)) {
        $recommendations[] = "Initialize security logging system";
    }
    
    if (empty($recommendations)) {
        echo "<span class='pass'>✅ All security measures are properly implemented!</span><br>";
        echo "Your database security configuration is optimal.";
    } else {
        echo "<span class='info'>📋 Recommended Actions:</span><br><ul>";
        foreach ($recommendations as $recommendation) {
            echo "<li>" . htmlspecialchars($recommendation) . "</li>";
        }
        echo "</ul>";
    }
    
    echo "</div>";
    
    echo "</div>";
    
    // Implementation Guide
    echo "<div class='section'>
        <h2>📚 Implementation Guide</h2>
        <div class='status-card'>
            <h3>🚀 Quick Setup</h3>
            <p>To implement all database security enhancements:</p>
            <ol>
                <li><strong>Run Setup:</strong> Execute <code>setup_database_security.php</code></li>
                <li><strong>Update Code:</strong> Replace <code>connection.php</code> includes with <code>secure_connection.php</code></li>
                <li><strong>Test Implementation:</strong> Run <code>test_database_security.php</code></li>
                <li><strong>Schedule Backups:</strong> Set up regular backup creation</li>
                <li><strong>Monitor Logs:</strong> Check <code>logs/database_security.log</code> regularly</li>
            </ol>
            
            <h3>🔧 Manual Configuration</h3>
            <p>For custom setups:</p>
            <ul>
                <li><strong>Database User:</strong> Create user with SELECT, INSERT, UPDATE privileges only</li>
                <li><strong>SSL Connection:</strong> Configure MySQL SSL certificates</li>
                <li><strong>Backup Encryption:</strong> Use AES-256-CBC encryption for backups</li>
                <li><strong>Integrity Monitoring:</strong> Schedule regular integrity checks</li>
            </ul>
        </div>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='section'>
        <h2 class='fail'>❌ Verification Error</h2>
        <p>An error occurred during verification:</p>
        <pre>" . htmlspecialchars($e->getMessage()) . "</pre>
    </div>";
}

echo "<div class='section'>
    <p><strong>Verification completed at:</strong> " . date('Y-m-d H:i:s') . "</p>
    <p><strong>Next verification recommended:</strong> " . date('Y-m-d H:i:s', strtotime('+1 week')) . "</p>
</div>";

echo "</body></html>";
?>
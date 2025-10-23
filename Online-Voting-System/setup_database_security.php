<?php
/**
 * Database Security Setup Script
 * Run this script to implement database security enhancements
 */

require_once __DIR__ . '/includes/DatabaseSecurity.php';

echo "<h2>Database Security Setup</h2>\n";
echo "<pre>\n";

try {
    $dbSecurity = new DatabaseSecurity();
    
    echo "1. Creating dedicated voting user with minimal privileges...\n";
    if ($dbSecurity->createVotingUser()) {
        echo "   ✅ Voting user created successfully\n";
    } else {
        echo "   ❌ Failed to create voting user\n";
    }
    
    echo "\n2. Testing secure connection...\n";
    $connection = $dbSecurity->getSecureConnection();
    if ($connection) {
        echo "   ✅ Secure connection established\n";
        
        // Test connection with a simple query
        $result = $connection->query("SELECT 1 as test");
        if ($result && $result->fetch_assoc()['test'] == 1) {
            echo "   ✅ Connection verified with test query\n";
        }
        
        $connection->close();
    } else {
        echo "   ❌ Failed to establish secure connection\n";
    }
    
    echo "\n3. Creating initial backup...\n";
    $backupFile = $dbSecurity->createSecureBackup();
    if ($backupFile) {
        echo "   ✅ Backup created: " . basename($backupFile) . "\n";
    } else {
        echo "   ❌ Failed to create backup\n";
    }
    
    echo "\n4. Performing integrity checks...\n";
    $integrityResult = $dbSecurity->performIntegrityChecks();
    echo "   Status: " . $integrityResult['status'] . "\n";
    
    if (!empty($integrityResult['issues'])) {
        echo "   Issues found:\n";
        foreach ($integrityResult['issues'] as $issue) {
            echo "   - " . $issue . "\n";
        }
    } else {
        echo "   ✅ No integrity issues found\n";
    }
    
    echo "\n5. Getting database statistics...\n";
    $stats = $dbSecurity->getDatabaseStats();
    if ($stats) {
        echo "   Table counts:\n";
        foreach ($stats['table_counts'] as $table => $count) {
            echo "   - {$table}: {$count} records\n";
        }
        
        echo "   Connection info:\n";
        echo "   - Host: " . $stats['connection_info']['host'] . "\n";
        echo "   - Server version: " . $stats['connection_info']['server_version'] . "\n";
    }
    
    echo "\n✅ Database security setup completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Update your application to use secure_connection.php instead of connection.php\n";
    echo "2. Set up regular backup schedule\n";
    echo "3. Monitor integrity check results\n";
    echo "4. Review security logs regularly\n";
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
}

echo "</pre>\n";
?>
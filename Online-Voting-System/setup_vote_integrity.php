<?php
/**
 * Vote Integrity Setup Script
 * 
 * This script sets up the database tables and initial configuration
 * required for the Vote Integrity and Audit Trail system.
 */

require_once "connection.php";
require_once "includes/VoteIntegrity.php";

echo "<h2>Vote Integrity and Audit Trail Setup</h2>\n";

try {
    // Initialize Vote Integrity system (creates tables)
    VoteIntegrity::initialize($con);
    echo "<p>✅ Vote Integrity system initialized successfully</p>\n";
    
    // Run migration for team_members table
    $migrationSQL = file_get_contents('migrations/add_team_members_table.sql');
    
    // Split SQL statements and execute them
    $statements = array_filter(array_map('trim', explode(';', $migrationSQL)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(--|USE|\/\*)/', $statement)) {
            if (mysqli_query($con, $statement)) {
                echo "<p>✅ Executed: " . substr($statement, 0, 50) . "...</p>\n";
            } else {
                echo "<p>⚠️ Warning: " . mysqli_error($con) . "</p>\n";
            }
        }
    }
    
    echo "<p>✅ Database migration completed successfully</p>\n";
    echo "<p><strong>Vote Integrity and Audit Trail system is now ready!</strong></p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ Error during setup: " . $e->getMessage() . "</p>\n";
}
?>
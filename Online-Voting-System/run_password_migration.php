<?php
/**
 * Database Migration Runner for Password Security Updates
 * Run this script once to update the database schema for secure password storage
 */

include "connection.php";

echo "<h2>Password Security Migration</h2>";
echo "<p>Running database migration to support secure password hashing...</p>";

try {
    // Start transaction
    mysqli_autocommit($con, FALSE);
    
    // Check if migration has already been run
    $checkColumn = mysqli_query($con, "SHOW COLUMNS FROM loginusers LIKE 'needs_password_reset'");
    if (mysqli_num_rows($checkColumn) > 0) {
        echo "<p style='color: orange;'>Migration appears to have already been run. Skipping...</p>";
        mysqli_autocommit($con, TRUE);
        exit();
    }
    
    echo "<p>1. Updating password field size to accommodate bcrypt hashes...</p>";
    $sql1 = "ALTER TABLE loginusers MODIFY COLUMN password VARCHAR(255) NOT NULL";
    if (!mysqli_query($con, $sql1)) {
        throw new Exception("Error updating password field: " . mysqli_error($con));
    }
    
    echo "<p>2. Adding password reset functionality fields...</p>";
    $sql2 = "ALTER TABLE loginusers ADD COLUMN reset_token VARCHAR(64) NULL DEFAULT NULL";
    if (!mysqli_query($con, $sql2)) {
        throw new Exception("Error adding reset_token field: " . mysqli_error($con));
    }
    
    $sql3 = "ALTER TABLE loginusers ADD COLUMN reset_token_expires DATETIME NULL DEFAULT NULL";
    if (!mysqli_query($con, $sql3)) {
        throw new Exception("Error adding reset_token_expires field: " . mysqli_error($con));
    }
    
    $sql4 = "ALTER TABLE loginusers ADD COLUMN password_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    if (!mysqli_query($con, $sql4)) {
        throw new Exception("Error adding password_changed_at field: " . mysqli_error($con));
    }
    
    echo "<p>3. Adding password reset flag...</p>";
    $sql5 = "ALTER TABLE loginusers ADD COLUMN needs_password_reset TINYINT(1) DEFAULT 0";
    if (!mysqli_query($con, $sql5)) {
        throw new Exception("Error adding needs_password_reset field: " . mysqli_error($con));
    }
    
    echo "<p>4. Adding index for reset token lookups...</p>";
    $sql6 = "ALTER TABLE loginusers ADD INDEX idx_reset_token (reset_token)";
    if (!mysqli_query($con, $sql6)) {
        throw new Exception("Error adding reset token index: " . mysqli_error($con));
    }
    
    echo "<p>5. Marking existing MD5 passwords for reset...</p>";
    $sql7 = "UPDATE loginusers SET needs_password_reset = 1 WHERE LENGTH(password) = 32";
    if (!mysqli_query($con, $sql7)) {
        throw new Exception("Error marking passwords for reset: " . mysqli_error($con));
    }
    
    // Commit transaction
    mysqli_commit($con);
    mysqli_autocommit($con, TRUE);
    
    echo "<p style='color: green;'><strong>Migration completed successfully!</strong></p>";
    echo "<p>The following security improvements have been implemented:</p>";
    echo "<ul>";
    echo "<li>Password field expanded to support bcrypt hashes (255 characters)</li>";
    echo "<li>Password reset functionality added with secure tokens</li>";
    echo "<li>Password change tracking implemented</li>";
    echo "<li>Existing MD5 passwords marked for mandatory reset</li>";
    echo "<li>Database indexes added for performance</li>";
    echo "</ul>";
    echo "<p><strong>Important:</strong> Users with existing accounts will need to reset their passwords for security reasons.</p>";
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($con);
    mysqli_autocommit($con, TRUE);
    echo "<p style='color: red;'><strong>Migration failed:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Database has been rolled back to previous state.</p>";
}

mysqli_close($con);
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
p { margin: 10px 0; }
ul { margin-left: 20px; }
</style>
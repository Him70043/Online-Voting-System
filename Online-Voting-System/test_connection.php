<?php
// Test database connection
include "connection.php";

echo "<h2>🔍 Database Connection Test</h2>";

if ($con) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if tables exist
    $tables = ['languages', 'loginusers', 'voters'];
    
    foreach ($tables as $table) {
        $result = mysqli_query($con, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
    
    // Show voting options
    $result = mysqli_query($con, "SELECT * FROM languages");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<h3>🗳️ Available Voting Options:</h3>";
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li><strong>" . $row['fullname'] . "</strong> - " . $row['about'] . " (Votes: " . $row['votecount'] . ")</li>";
        }
        echo "</ul>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_error($con) . "</p>";
}
?>
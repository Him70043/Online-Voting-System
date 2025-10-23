<?php
include "connection.php";

echo "<h2>🗄️ Creating All Required Database Tables</h2>";

// Create team_members table
$create_team_members = "
CREATE TABLE IF NOT EXISTS team_members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    about TEXT,
    votecount INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $create_team_members)) {
    echo "<p style='color: green;'>✅ team_members table created/verified</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating team_members table: " . mysqli_error($con) . "</p>";
}

// Create team_member_votes table
$create_team_votes = "
CREATE TABLE IF NOT EXISTS team_member_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    member_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (username)
)";

if (mysqli_query($con, $create_team_votes)) {
    echo "<p style='color: green;'>✅ team_member_votes table created/verified</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating team_member_votes table: " . mysqli_error($con) . "</p>";
}

// Verify languages table exists
$check_languages = mysqli_query($con, "SHOW TABLES LIKE 'languages'");
if (mysqli_num_rows($check_languages) > 0) {
    echo "<p style='color: green;'>✅ languages table exists</p>";
} else {
    echo "<p style='color: red;'>❌ languages table missing</p>";
}

// Verify voters table exists
$check_voters = mysqli_query($con, "SHOW TABLES LIKE 'voters'");
if (mysqli_num_rows($check_voters) > 0) {
    echo "<p style='color: green;'>✅ voters table exists</p>";
} else {
    echo "<p style='color: red;'>❌ voters table missing</p>";
}

// Verify loginusers table exists
$check_loginusers = mysqli_query($con, "SHOW TABLES LIKE 'loginusers'");
if (mysqli_num_rows($check_loginusers) > 0) {
    echo "<p style='color: green;'>✅ loginusers table exists</p>";
} else {
    echo "<p style='color: red;'>❌ loginusers table missing</p>";
}

echo "<h3>📊 Database Tables Status:</h3>";
$tables_result = mysqli_query($con, "SHOW TABLES");
if ($tables_result) {
    echo "<ul>";
    while ($table = mysqli_fetch_array($tables_result)) {
        echo "<li>" . $table[0] . "</li>";
    }
    echo "</ul>";
}

echo "<h3>🗳️ Sample Data Check:</h3>";

// Check team members data
$team_count = mysqli_query($con, "SELECT COUNT(*) as count FROM team_members");
$team_row = mysqli_fetch_assoc($team_count);
echo "<p>Team members: " . $team_row['count'] . " records</p>";

// Check languages data
$lang_count = mysqli_query($con, "SELECT COUNT(*) as count FROM languages");
$lang_row = mysqli_fetch_assoc($lang_count);
echo "<p>Programming languages: " . $lang_row['count'] . " records</p>";

echo "<br><div style='text-align: center;'>";
echo "<a href='multi_question_voter.php' style='margin: 10px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>🗳️ Go to Multi-Question Voting</a>";
echo "<a href='multi_question_results.php' style='margin: 10px; padding: 10px 20px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px;'>📊 View Results</a>";
echo "<a href='index.php' style='margin: 10px; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;'>🏠 Home</a>";
echo "</div>";
?>
<?php
include "connection.php";

echo "<h2>🧹 Cleaning Up Voting Options</h2>";

// Step 1: Clean up languages table - remove team members and keep only programming languages
echo "<h3>Step 1: Cleaning Programming Languages Table</h3>";

// First, let's see what's currently in the languages table
$result = mysqli_query($con, "SELECT * FROM languages ORDER BY fullname");
echo "<h4>Current entries in languages table:</h4>";
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p>ID: " . $row['lan_id'] . " - " . htmlspecialchars($row['fullname']) . " (" . $row['votecount'] . " votes)</p>";
    }
}

// Delete all entries from languages table
mysqli_query($con, "DELETE FROM languages");
echo "<p style='color: blue;'>🔄 Cleared all entries from languages table</p>";

// Reset auto increment
mysqli_query($con, "ALTER TABLE languages AUTO_INCREMENT = 1");

// Insert only the correct programming languages
$programming_languages = [
    ['.NET', 'Microsoft .NET Framework'],
    ['C++', 'C++ Programming Language'],
    ['JAVA', 'Java Programming Language'],
    ['PHP', 'PHP Programming Language'],
    ['PYTHON', 'Python Programming Language']
];

echo "<h4>Adding correct programming languages:</h4>";
foreach ($programming_languages as $lang) {
    $insert_stmt = mysqli_prepare($con, "INSERT INTO languages (fullname, about, votecount) VALUES (?, ?, 0)");
    mysqli_stmt_bind_param($insert_stmt, "ss", $lang[0], $lang[1]);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo "<p style='color: green;'>✅ Added: " . htmlspecialchars($lang[0]) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add: " . htmlspecialchars($lang[0]) . "</p>";
    }
    mysqli_stmt_close($insert_stmt);
}

// Step 2: Clean up team_members table
echo "<h3>Step 2: Cleaning Team Members Table</h3>";

// Create team_members table if it doesn't exist
$create_table_sql = "
CREATE TABLE IF NOT EXISTS team_members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    about TEXT,
    votecount INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $create_table_sql)) {
    echo "<p style='color: green;'>✅ Team members table ready</p>";
}

// Clear existing team members
mysqli_query($con, "DELETE FROM team_members");
echo "<p style='color: blue;'>🔄 Cleared all entries from team_members table</p>";

// Reset auto increment
mysqli_query($con, "ALTER TABLE team_members AUTO_INCREMENT = 1");

// Insert only the correct team members with updated descriptions
$team_members = [
    ['Himanshu', 'Team Leader & Full Stack Developer'],
    ['Praffull', 'Backend Developer & Database Expert'],
    ['Bhavesh', 'Team Member - Developer and contributor'],
    ['Eve', 'Team Member - Developer and contributor']
];

echo "<h4>Adding correct team members:</h4>";
foreach ($team_members as $member) {
    $insert_stmt = mysqli_prepare($con, "INSERT INTO team_members (fullname, about, votecount) VALUES (?, ?, 0)");
    mysqli_stmt_bind_param($insert_stmt, "ss", $member[0], $member[1]);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo "<p style='color: green;'>✅ Added: " . htmlspecialchars($member[0]) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add: " . htmlspecialchars($member[0]) . "</p>";
    }
    mysqli_stmt_close($insert_stmt);
}

// Step 3: Clean up vote tracking tables
echo "<h3>Step 3: Cleaning Vote Tracking Tables</h3>";

// Clear existing votes to start fresh
mysqli_query($con, "DELETE FROM votes");
mysqli_query($con, "DELETE FROM team_member_votes");
echo "<p style='color: blue;'>🔄 Cleared all existing votes to start fresh</p>";

// Step 4: Show final results
echo "<h3>📊 Final Clean Tables:</h3>";

echo "<h4>Programming Languages (Question 1):</h4>";
$result = mysqli_query($con, "SELECT * FROM languages ORDER BY fullname");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr style='background: #e3f2fd;'><th>ID</th><th>Language</th><th>Description</th><th>Votes</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['lan_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['about']) . "</td>";
        echo "<td>" . htmlspecialchars($row['votecount']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h4>Team Members (Question 2):</h4>";
$result = mysqli_query($con, "SELECT * FROM team_members ORDER BY fullname");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f3e5f5;'><th>ID</th><th>Member</th><th>Description</th><th>Votes</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['member_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['about']) . "</td>";
        echo "<td>" . htmlspecialchars($row['votecount']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3 style='color: #155724; margin: 0;'>✅ Cleanup Complete!</h3>";
echo "<p style='color: #155724; margin: 5px 0 0 0;'>Your voting system now has the correct options:</p>";
echo "<ul style='color: #155724;'>";
echo "<li><strong>Question 1:</strong> 5 Programming Languages (.NET, C++, JAVA, PHP, PYTHON)</li>";
echo "<li><strong>Question 2:</strong> 4 Team Members (Himanshu, Praffull, Bhavesh, Eve)</li>";
echo "</ul>";
echo "</div>";

echo "<br><div style='text-align: center;'>";
echo "<a href='multi_question_voter.php' style='margin: 10px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>🗳️ Test Voting</a>";
echo "<a href='multi_question_results.php' style='margin: 10px; padding: 10px 20px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px;'>📊 View Results</a>";
echo "<a href='index.php' style='margin: 10px; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;'>🏠 Home</a>";
echo "</div>";
?>
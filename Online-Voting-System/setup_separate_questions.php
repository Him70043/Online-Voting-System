<?php
include "connection.php";

echo "<h2>🗳️ Setting up Separate Voting Questions</h2>";

// Create team_members table for the second question
$create_table_sql = "
CREATE TABLE IF NOT EXISTS team_members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    about TEXT,
    votecount INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $create_table_sql)) {
    echo "<p style='color: green;'>✅ Team members table created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating table: " . mysqli_error($con) . "</p>";
}

// Insert team members
$team_members = [
    ['Himanshu (Team Leader)', 'Project coordinator and lead developer'],
    ['Bhavesh', 'Team Member - Developer and contributor'],
    ['Praffull', 'Team Member - Developer and contributor'],
    ['Eve', 'Team Member - Developer and contributor']
];

echo "<h3>Adding Team Members:</h3>";
foreach ($team_members as $member) {
    // Check if member already exists
    $check_stmt = mysqli_prepare($con, "SELECT member_id FROM team_members WHERE fullname = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $member[0]);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) == 0) {
        // Insert new team member
        $insert_stmt = mysqli_prepare($con, "INSERT INTO team_members (fullname, about, votecount) VALUES (?, ?, 0)");
        mysqli_stmt_bind_param($insert_stmt, "ss", $member[0], $member[1]);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            echo "<p style='color: green;'>✅ Added: " . htmlspecialchars($member[0]) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add: " . htmlspecialchars($member[0]) . "</p>";
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        echo "<p style='color: orange;'>⚠️ Already exists: " . htmlspecialchars($member[0]) . "</p>";
    }
    mysqli_stmt_close($check_stmt);
}

// Remove team members from languages table (they should only be in team_members table)
echo "<h3>Cleaning up languages table:</h3>";
$team_names = ['Himanshu (Team Leader)', 'Bhavesh', 'Praffull', 'Eve'];
foreach ($team_names as $name) {
    $delete_stmt = mysqli_prepare($con, "DELETE FROM languages WHERE fullname = ?");
    mysqli_stmt_bind_param($delete_stmt, "s", $name);
    if (mysqli_stmt_execute($delete_stmt)) {
        if (mysqli_affected_rows($con) > 0) {
            echo "<p style='color: blue;'>🔄 Removed " . htmlspecialchars($name) . " from languages table</p>";
        }
    }
    mysqli_stmt_close($delete_stmt);
}

echo "<h3>📊 Current Tables Status:</h3>";

// Show languages table
echo "<h4>Programming Languages:</h4>";
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

// Show team_members table
echo "<h4>Team Members:</h4>";
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
} else {
    echo "<p style='color: red;'>Error: " . mysqli_error($con) . "</p>";
}

echo "<br><div style='text-align: center;'>";
echo "<a href='index.php' style='margin: 10px; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;'>🏠 Home</a>";
echo "<a href='multi_question_voter.php' style='margin: 10px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>🗳️ Vote (Multi-Question)</a>";
echo "<a href='multi_question_results.php' style='margin: 10px; padding: 10px 20px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px;'>📊 View Results</a>";
echo "</div>";
?>
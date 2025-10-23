<?php
include "connection.php";
session_start();

echo "<h2>🔍 System Diagnostics</h2>";

// Check database connection
if ($con) {
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection: FAILED</p>";
    exit();
}

// Check session
echo "<h3>Session Information:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<p>✅ User logged in: " . htmlspecialchars($_SESSION['SESS_NAME'] ?? 'Unknown') . "</p>";
    echo "<p>User ID: " . htmlspecialchars($_SESSION['user_id']) . "</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No user session found</p>";
}

// Check tables
echo "<h3>Database Tables Check:</h3>";
$tables = ['languages', 'team_members', 'voters', 'loginusers', 'team_member_votes'];

foreach ($tables as $table) {
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = mysqli_fetch_assoc($result)['count'];
        echo "<p>✅ Table '$table': $count records</p>";
    } else {
        echo "<p style='color: red;'>❌ Table '$table': ERROR - " . mysqli_error($con) . "</p>";
    }
}

// Check team_members data specifically
echo "<h3>Team Members Data:</h3>";
$result = mysqli_query($con, "SELECT * FROM team_members");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Votes</th></tr>";
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
    echo "<p style='color: red;'>❌ No team members found!</p>";
    
    // Try to add team members
    echo "<h4>🔧 Adding Team Members:</h4>";
    $team_members = [
        ['Himanshu', 'Team Leader & Full Stack Developer'],
        ['Praffull', 'Backend Developer & Database Expert'],
        ['Bhavesh', 'Team Member - Developer and contributor'],
        ['Eve', 'Team Member - Developer and contributor']
    ];
    
    foreach ($team_members as $member) {
        $stmt = mysqli_prepare($con, "INSERT INTO team_members (fullname, about, votecount) VALUES (?, ?, 0)");
        mysqli_stmt_bind_param($stmt, "ss", $member[0], $member[1]);
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✅ Added: " . htmlspecialchars($member[0]) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add: " . htmlspecialchars($member[0]) . "</p>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Check languages data
echo "<h3>Programming Languages Data:</h3>";
$result = mysqli_query($con, "SELECT * FROM languages");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Language</th><th>Description</th><th>Votes</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['lan_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($row['about']) . "</td>";
        echo "<td>" . htmlspecialchars($row['votecount']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No programming languages found!</p>";
}

// Test simple SQL query
echo "<h3>Simple SQL Test:</h3>";
$result = mysqli_query($con, "SHOW TABLES");
if ($result) {
    echo "<p style='color: green;'>✅ SQL queries working</p>";
    echo "<p>Tables in database:</p><ul>";
    while ($row = mysqli_fetch_array($result)) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ SQL queries not working: " . mysqli_error($con) . "</p>";
}

echo "<hr>";
echo "<h3>🔗 Quick Links:</h3>";
echo "<p><a href='no_csrf_admin.php?pass=himanshu123'>🔧 No CSRF Admin Panel</a></p>";
echo "<p><a href='multi_question_voter.php'>🗳️ Multi-Question Voting</a></p>";
echo "<p><a href='simple_login.php'>🔐 Login</a></p>";
echo "<p><a href='index.php'>🏠 Home</a></p>";
?>
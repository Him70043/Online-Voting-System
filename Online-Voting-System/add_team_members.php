<?php
include "connection.php";

// Add new voting options for team members
$team_members = [
    [
        'fullname' => 'Himanshu (Team Leader)',
        'about' => 'Team Leader - Project coordinator and lead developer'
    ],
    [
        'fullname' => 'Bhavesh',
        'about' => 'Team Member - Developer and contributor'
    ],
    [
        'fullname' => 'Praffull',
        'about' => 'Team Member - Developer and contributor'
    ],
    [
        'fullname' => 'Eve',
        'about' => 'Team Member - Developer and contributor'
    ]
];

echo "<h2>Adding Team Member Voting Options...</h2>";

foreach ($team_members as $member) {
    // Check if member already exists
    $check_stmt = mysqli_prepare($con, "SELECT lan_id FROM languages WHERE fullname = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $member['fullname']);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) == 0) {
        // Insert new team member
        $insert_stmt = mysqli_prepare($con, "INSERT INTO languages (fullname, about, votecount) VALUES (?, ?, 0)");
        mysqli_stmt_bind_param($insert_stmt, "ss", $member['fullname'], $member['about']);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            echo "<p style='color: green;'>✅ Added: " . htmlspecialchars($member['fullname']) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add: " . htmlspecialchars($member['fullname']) . "</p>";
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        echo "<p style='color: orange;'>⚠️ Already exists: " . htmlspecialchars($member['fullname']) . "</p>";
    }
    mysqli_stmt_close($check_stmt);
}

echo "<h3>Current Voting Options:</h3>";
$result = mysqli_query($con, "SELECT * FROM languages ORDER BY fullname");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Description</th><th>Votes</th></tr>";
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

echo "<br><a href='index.php'>← Back to Home</a>";
echo "<br><a href='simple_voter.php'>🗳️ Go Vote</a>";
echo "<br><a href='simple_results.php'>📊 View Results</a>";
?>